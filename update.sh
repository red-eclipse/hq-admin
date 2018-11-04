#!/bin/bash

umask 007

UPDDIR=$(dirname "${0}")
UPDTEMP="${UPDDIR}/temp"
UPDLOG="${UPDTEMP}/log.txt"

. "${UPDDIR}/private.sh"

if [ ! -d "${UPDTEMP}" ]; then
    mkdir -p "${UPDTEMP}"
fi

LOCKFILE="${UPDTEMP}/update.lock"
if [ -e "${LOCKFILE}" ]; then
    LOCKPID=`cat "${LOCKFILE}"`
    if [ -n "${LOCKPID}" ]; then
        LOCKCHK=`ps ax | grep "^${LOCKPID} "`
        if [ -n "${LOCKCHK}" ]; then
            exit 0
        fi
    fi
fi
echo "$$" > "${LOCKFILE}"

VIRTFILE="/etc/postfix/virtual"
ACTVFILE="${UPDDIR}/active.cfg"
AUTHFILE="${UPDDIR}/auth.cfg"

AUTHPREV="${UPDTEMP}/prev.cfg"
AUTHTEMP="${UPDTEMP}/auth.tmp"
VIRTTEMP="${UPDTEMP}/virtual"
SFTPFILE="${UPDTEMP}/sftp.cfg"
MEMBFILE="${UPDTEMP}/members.cfg"
PURGFILE="${UPDTEMP}/purge.cfg"
ADDUFILE="${UPDTEMP}/adduser.cfg"
DELUFILE="${UPDTEMP}/deluser.cfg"

SENDMAIL="/usr/sbin/sendmail -bm -N failure"
SFTPCONN="${UPDPASS} sftp -oBatchMode=no -oStrictHostKeyChecking=no"
RESERVER="${UPDPASS} ssh -oBatchMode=no -oStrictHostKeyChecking=no"
RECMDS="killall -HUP redeclipse_server_linux"

AUTHGEN="${UPDDIR}/genkey_linux"
AUTHSTR=("1" "2" "3" "4" "5" "6" "7" "8" "9" "0" "q" "w" "e" "r" "t" "y" "u" "i" "o" "p" "a" "s" "d" "f" "g" "h" "j" "k" "l" "z" "x" "c" "v" "b" "n" "m" "Q" "W" "E" "R" "T" "Y" "U" "I" "O" "P" "A" "S" "D" "F" "G" "H" "J" "K" "L" "Z" "X" "C" "V" "B" "N" "M")

logtext() {
    LOGDATE=$(date "+%b %d %X")
    if [ -n "${1}" ]; then
        LOGTEXT="${1}"
    else
        read LOGTEXT
    fi
    echo "${LOGTEXT}"
    echo "${LOGDATE} ${LOGTEXT}" >> "${UPDLOG}"
}

sanitize_list() {
    if [ -n "${1}" ]; then
        SANITYIN="${1}"
    else
        read SANITYIN
    fi
    if [ -n "${SANITYIN}" ]; then
        echo "${SANITYIN}" | sed -e "s/\t/ /g;s/ \([ ]*\)/ /g;s/^ //g;s/ $//g"
    fi
}

send_email() {
    EMAILDATE=$(date | sed -e 's/  / /g')
    EMAILBOUND=$(head -c 64 /dev/urandom | shasum | cut -d' ' -f1)
    EMAILSED="s/~USERNAME~/${2}/g;s/~USERMAIL~/${3}/g;s/~DATE~/${EMAILDATE}/g;s/~BOUNDARY~/${EMAILBOUND}/g"
    if [ -n "${4}" ]; then
        EMAILSED="${EMAILSED};${4}"
    fi
    sed -e "${EMAILSED}" "${1}" | ${SENDMAIL} "${3}" 2>&1 | logtext
}

AUTHCACHE=`cat "${AUTHFILE}"`
echo "${AUTHCACHE}" > "${AUTHTEMP}"

DELCOUNT=0
process_delete() {
    DELLIST=`echo "${2}" | sanitize_list | sort | uniq`
    for i in ${DELLIST}; do
        if [ -n "${i}" ]; then
            DELLINE=`echo "${AUTHCACHE}" | grep "^addauth \([^ ]*\) \([^ ]*\) \([^ ]*\) ${i} \([^ ]*\)$"`
            if [ -n "${DELLINE}" ]; then
                DELUSER=`echo "${DELLINE}" | cut -d" " -f2`
                DELSKIP=0
                if [ "${1}" != "delete" ]; then
                    DELFLAG=`echo "${DELLINE}" | cut -d" " -f3`
                    if [ "${DELFLAG}" != "u" ] && [ "${DELFLAG}" != "s" ]; then
                        #logtext "NOTICE: '${1}' for ${DELUSER} <${i}> but flag '${DELFLAG}' prevents deletion."
                        DELSKIP=1
                    fi
                fi
                if [ "${DELSKIP}" = 0 ]; then
                    logtext "Removing: ${DELUSER} <${i}> (${1})"
                    AUTHCACHE=`echo "${AUTHCACHE}" | grep -v "^addauth ${DELUSER} "`
                    echo "${AUTHCACHE}" > "${AUTHTEMP}"
                    DELCACHE=`grep -v "^${DELUSER}$" "${ACTVFILE}"`
                    echo "${DELCACHE}" > "${ACTVFILE}"
                    VIRTCACHE=`grep -v " ${i}$" "${VIRTFILE}"`
                    echo "${VIRTCACHE}" > "${VIRTFILE}"
                    logtext "${i}" >> "${PURGFILE}"
                    DELCOUNT=$(( DELCOUNT + 1 ))
                fi
            fi
        fi
    done
}

if [ -e "${DELUFILE}" ]; then
    DELREQS=`cat "${DELUFILE}" | tr "\n" " "`
    if [ -n "${DELREQS}" ]; then
        logtext "Processing deletion requests..."
        process_delete "delete" "${DELREQS}"
    fi
    rm -f "${DELUFILE}"
fi

BOUNCES=`grep "^.*to=<.*status=bounced.*$" "/var/log/mail.log" | grep -v "[Ss][Pp][Aa][Mm]" | sed -e "s/^.*to=<\([^>]*\)>.*$/\1/" | grep "^\([^@]*\)@\([^.]*\).\(.*\)$" | grep -v "redeclipse.net"`
if [ -n "${BOUNCES}" ]; then
    process_delete "bounce" "${BOUNCES}"
fi

EXPIRES=`grep "status=expired" /var/log/mail.log | grep -v "[Ss][Pp][Aa][Mm]" | cut -d" " -f6 | sed -e "s/^\([^:]*\):$/\1/" | sort | uniq`
if [ -n "${EXPIRES}" ]; then
    for i in ${EXPIRES}; do
        EXPIRED=`grep "^.* ${i}: to=<.*>.*$" "/var/log/mail.log" | sed -e "s/^.*to=<\([^>]*\)>.*$/\1/" | grep "^\([^@]*\)@\([^.]*\).\(.*\)$" | grep -v "redeclipse.net"`
        if [ -n "${EXPIRED}" ]; then
            process_delete "expire" "${EXPIRED}"
        fi
    done
fi

if [ "${DELCOUNT}" -gt 0 ]; then
    logtext "Purging ${DELCOUNT} user(s)..."
    /usr/sbin/remove_members --fromall --nouserack --file="${PURGFILE}" 2>&1 | logtext
    rm -f "${PURGFILE}"
fi

ADDCOUNT=0
PRGCOUNT=0
if [ -e "${ADDUFILE}" ]; then
    a=`cat "${ADDUFILE}" | sort | uniq`
    if [ -n "${a}" ]; then
        b=`echo "${a}" | wc -l`
        for (( c=0; ${c} < ${b}; c=$(( c + 1 )) )); do
            ADDINPUT[${c}]=`echo "${a}" | sed -n "$(( c + 1 ))p" | sanitize_list`
        done
    fi
    if [ ${#ADDINPUT[@]} != 0 ]; then
        for v in ${!ADDINPUT[@]}; do
            ADDLINE="${ADDINPUT[v]}"
            if [ -n "${ADDLINE}" ]; then
                ADDMAIL=`echo "${ADDLINE}" | cut -d" " -f1`
                ADDUSER=`echo "${ADDLINE}" | cut -d" " -f2`
                ADDFLAG=`echo "${ADDLINE}" | cut -d" " -f3`
                if [ -z "${ADDFLAG}" ]; then
                    ADDFLAG="u"
                fi
                ADDSID=`echo "${ADDLINE}" | cut -d" " -f4`
                if [ -z "${ADDFLAG}" ]; then
                    ADDSID="0"
                fi
                logtext "Checking: ${ADDUSER} (${ADDFLAG}) <${ADDMAIL}> "
                ADDFIND=`echo "${AUTHCACHE}" | grep "^addauth \([^ ]*\) \([^ ]*\) \([^ ]*\) ${ADDMAIL} \([^ ]*\)$"`
                if [ -n "${ADDFIND}" ]; then
                    ADDCHKUSER=`echo "${ADDFIND}" | cut -d" " -f2 | tail -n 1`;
                    ADDCHKFLAG=`echo "${ADDFIND}" | cut -d" " -f3 | tail -n 1`;
                    ADDCHKSID=`echo "${ADDFIND}" | cut -d" " -f6 | tail -n 1`;
                    ADDSKEY=`echo "${ADDFIND}" | cut -d" " -f4 | tail -n 1`;
                    if [ "${ADDCHKUSER}" != "${ADDUSER}" ] || [ "${ADDCHKFLAG}" != "${ADDFLAG}" ] || [ "${ADDCHKSID}" != "${ADDSID}" ]; then
                        PRGCOUNT=$(( PRGCOUNT + 1 ))
                        logtext "Update: ${ADDCHKUSER} -> ${ADDUSER} flag: ${ADDCHKFLAG} -> ${ADDFLAG} sid: ${ADDCHKSID} -> ${ADDSID}"
                        AUTHCACHE=`echo "${AUTHCACHE}" | grep -v "^addauth \([^ ]*\) \([^ ]*\) \([^ ]*\) ${ADDMAIL} \([^ ]*\)$"`
                        echo "${AUTHCACHE}" > "${AUTHTEMP}"
                        VIRTCACHE=`grep -v " ${ADDMAIL}$" "${VIRTFILE}"`
                        echo "${VIRTCACHE}" > "${VIRTFILE}"
                        ADDPURGE=`echo "${ADDFIND}" | cut -d" " -f2 | tr "\n" " " | sanitize_list`
                        for j in ${ADDPURGE}; do
                            DELCACHE=`grep -v "^${j}$" "${ACTVFILE}"`
                            echo "${DELCACHE}" > "${ACTVFILE}"
                        done
                        AUTHCACHE=`echo -e "${AUTHCACHE}\naddauth ${ADDUSER} ${ADDFLAG} ${ADDSKEY} ${ADDMAIL} ${ADDSID}"`
                        echo "${ADDUSER}@redeclipse.net ${ADDMAIL}" >> "${VIRTFILE}"
                        echo "${ADDUSER}" >> "${ACTVFILE}"
                        ADDCOUNT=$(( ADDCOUNT + 1 ))
                    else
                        logtext "Skip: Email exists."
                        send_email "${UPDDIR}/mail/exists" "${ADDUSER}" "${ADDMAIL}"
                    fi
                else
                    ADDFIND=`echo "${AUTHCACHE}" | grep "^addauth ${ADDUSER} "`
                    if [ -n "${ADDFIND}" ]; then
                        q=1
                        r="${ADDUSER}"
                        while [ -n "${ADDFIND}" ]; do
                            ADDUSER="${r}${q}"
                            ADDFIND=`echo "${AUTHCACHE}" | grep "^addauth ${ADDUSER} "`
                            q=$(( q + 1 ))
                        done
                        logtext "Renamed to: ${ADDUSER} "
                    fi
                    w=""
                    x=$(( (RANDOM % 64) + 64 ))
                    for (( y=0; ${y} < ${x}; y=$(( y + 1 )) )); do
                        z=$(( RANDOM % ${#AUTHSTR[@]} ))
                        w="${w}${AUTHSTR[z]}"
                    done
                    ADDKEYS=`${AUTHGEN} "${w}" | cut -d" " -f3 | tr "\n" " "`
                    ADDUKEY=`echo "${ADDKEYS}" | cut -d" " -f1`
                    ADDSKEY=`echo "${ADDKEYS}" | cut -d" " -f2`
                    logtext "OK: Generated keys, adding user - ${ADDKEYS}"
                    AUTHCACHE=`echo -e "${AUTHCACHE}\naddauth ${ADDUSER} ${ADDFLAG} ${ADDSKEY} ${ADDMAIL} ${ADDSID}"`
                    send_email "${UPDDIR}/mail/reply" "${ADDUSER}" "${ADDMAIL}" "s/~USERKEY~/${ADDUKEY}/g"
                    ADDCOUNT=$(( ADDCOUNT + 1 ))
                fi
            fi
        done
    fi
    if [ "${ADDCOUNT}" -gt 0 ]; then
        logtext "Added ${ADDCOUNT} user(s)..."
        echo "${AUTHCACHE}" | sort | uniq > "${AUTHTEMP}"
    fi
    rm -f "${ADDUFILE}"
fi

AUTHDIFF=`diff "${AUTHTEMP}" "${AUTHPREV}"`
if [ -n "${AUTHDIFF}" ]; then
    logtext "Transmitting updates..."
    logtext "${AUTHDIFF}"
    cp -fv "${AUTHTEMP}" "${AUTHFILE}"
    echo -e "put \"${AUTHFILE}\" \"${UPDAUTH}\"" > "${SFTPFILE}"
    ${SFTPCONN} -b "${SFTPFILE}" "${UPDHOST}" && ${RESERVER} "${UPDHOST}" "${RECMDS}" && cp -f "${AUTHFILE}" "${AUTHPREV}" 2>&1 | logtext
fi
rm -f "${AUTHTEMP}"

USERCOUNT=0
USERLIST=`echo "${AUTHCACHE}" | grep "^addauth " | sed -e "s/^\([^ ]*\) \([^ ]*\) \([^ ]*\) \([^ ]*\) \([^ ]*\).*$/\2 \5/"`
if [ -n "${USERLIST}" ]; then
    NUMLINES=`echo "${USERLIST}" | wc -l`
    rm -f "${MEMBFILE}"
    CURLINE=1
    while [ "${CURLINE}" -le "${NUMLINES}" ]; do
        USERLINE=`echo "${USERLIST}" | sed -n "${CURLINE}p" | sanitize_list`
        USERNAME=`echo "${USERLINE}" | cut -d" " -f1`
        USERMAIL=`echo "${USERLINE}" | cut -d" " -f2`
        USERACTV=`grep "^${USERNAME}$" "${ACTVFILE}"`
        if [ -z "${USERACTV}" ]; then
            logtext "New Member: ${USERNAME} <${USERMAIL}>"
            echo "${USERNAME}" >> "${ACTVFILE}"
            echo "${USERMAIL}" >> "${MEMBFILE}"
            echo "${USERNAME}@redeclipse.net ${USERMAIL}" >> "${VIRTFILE}"
            USERCOUNT=$(( USERCOUNT + 1 ))
        fi
        CURLINE=$(( CURLINE + 1 ))
    done
    if [ "${USERCOUNT}" -gt 0 ]; then
        logtext "Processing ${USERCOUNT} user(s)..."
        /usr/sbin/add_members -r "${MEMBFILE}" -w "n" "news" 2>&1 | logtext
    fi
fi

if [ "${DELCOUNT}" -gt 0 ] || [ "${PRGCOUNT}" -gt 0 ] || [ "${USERCOUNT}" -gt 0 ]; then
    /usr/sbin/postmap "${VIRTFILE}" 2>&1 | logtext
fi

rm -f "${LOCKFILE}"
chown -R root:www-data "${UPDDIR}" > /dev/null
chmod -R ug+rw "${UPDDIR}" > /dev/null
