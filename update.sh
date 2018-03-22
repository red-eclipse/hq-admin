#!/bin/bash

umask 007

UPDDIR=$(dirname "${0}")
UPDTEMP="${UPDDIR}/temp"

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

sort -b "${AUTHFILE}" > "${AUTHTEMP}"

DELCOUNT=0
process_delete() {
    DELLIST=`echo "${2}" | sanitize_list`
    for i in ${DELLIST}; do
        if [ -n "${i}" ]; then
            DELLINE=`grep "^addauth \([^ ]*\) \([^ ]*\) \([^ ]*\) ${i}$" "${AUTHTEMP}"`
            if [ -n "${DELLINE}" ]; then
                DELUSER=`echo "${DELLINE}" | cut -d" " -f2`
                DELSKIP=0
                if [ "${1}" != "delete" ]; then
                    DELFLAG=`echo "${DELLINE}" | cut -d" " -f3`
                    if [ "${DELFLAG}" != "u" ] && [ "${DELFLAG}" != "s" ]; then
                        echo "NOTICE: '${1}' for ${DELUSER} <${i}> but flag '${DELFLAG}' prevents deletion."
                        DELSKIP=1
                    fi
                fi
                if [ "${DELSKIP}" = 0 ]; then
                    echo "Removing: ${DELUSER} <${i}> (${1})"
                    grep -v "^addauth ${DELUSER} " "${AUTHTEMP}" > "${AUTHTEMP}.int"
                    mv -f "${AUTHTEMP}.int" "${AUTHTEMP}"
                    grep -v "^${DELUSER}$" "${ACTVFILE}" > "${ACTVFILE}.int"
                    mv -f "${ACTVFILE}.int" "${ACTVFILE}"
                    grep -v " ${i}$" "${VIRTFILE}" > "${VIRTTEMP}.int"
                    mv -f "${VIRTTEMP}.int" "${VIRTFILE}"
                    echo "${i}" >> "${PURGFILE}"
                    DELCOUNT=$(( DELCOUNT + 1 ))
                fi
            fi
        fi
    done
}

if [ -e "${DELUFILE}" ]; then
    DELREQS=`cat "${DELUFILE}" | tr "\n" " "`
    if [ -n "${DELREQS}" ]; then
        echo "Processing deletion requests..."
        process_delete "delete" "${DELREQS}"
    fi
    rm -f "${DELUFILE}"
fi

BOUNCES=`grep "^.*to=<.*status=bounced.*$" "/var/log/mail.log" | sed -e "s/^.*to=<\([^>]*\)>.*$/\1/" | grep "^\([^@]*\)@\([^.]*\).\(.*\)$" | grep -v "redeclipse.net"`
if [ -n "${BOUNCES}" ]; then
    process_delete "bounce" "${BOUNCES}"
fi

EXPIRES=`grep "status=expired" /var/log/mail.log | cut -d" " -f6 | sed -e "s/^\([^:]*\):$/\1/"`
if [ -n "${EXPIRES}" ]; then
    for i in ${EXPIRES}; do
        EXPIRED=`grep "^.* ${i}: to=<.*>.*$" "/var/log/mail.log" | sed -e "s/^.*to=<\([^>]*\)>.*$/\1/" | grep "^\([^@]*\)@\([^.]*\).\(.*\)$" | grep -v "redeclipse.net" | tail -n 1`
        if [ -n "${EXPIRED}" ]; then
            process_delete "expire" "${EXPIRED}"
        fi
    done
fi

if [ "${DELCOUNT}" -gt 0 ]; then
    echo "Purging ${DELCOUNT} user(s)..."
    /usr/sbin/remove_members --fromall --nouserack --file="${PURGFILE}"
    rm -f "${PURGFILE}"
fi

ADDCOUNT=0
PRGCOUNT=0
if [ -e "${ADDUFILE}" ]; then
    a=`cat "${ADDUFILE}"`
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
                echo -n "Checking: ${ADDUSER} (${ADDFLAG}) <${ADDMAIL}> "
                ADDFIND=`grep "^addauth \([^ ]*\) \([^ ]*\) \([^ ]*\) ${ADDMAIL}$" "${AUTHTEMP}"`
                if [ -n "${ADDFIND}" ]; then
                    ADDCHKUSER=`echo "${ADDFIND}" | cut -d" " -f2 | tail -n 1`;
                    ADDCHKFLAG=`echo "${ADDFIND}" | cut -d" " -f3 | tail -n 1`;
                    ADDSKEY=`echo "${ADDFIND}" | cut -d" " -f4 | tail -n 1`;
                    if [ "${ADDCHKUSER}" != "${ADDUSER}" ] || [ "${ADDCHKFLAG}" != "${ADDFLAG}" ]; then
                        PRGCOUNT=$(( PRGCOUNT + 1 ))
                        echo "update: ${ADDCHKUSER} -> ${ADDUSER} flag: ${ADDCHKFLAG} -> ${ADDFLAG}"
                        grep -v "^addauth \([^ ]*\) \([^ ]*\) \([^ ]*\) ${ADDMAIL}$" "${AUTHTEMP}" > "${AUTHTEMP}.int"
                        mv -f "${AUTHTEMP}.int" "${AUTHTEMP}"
                        grep -v " ${ADDMAIL}$" "${VIRTFILE}" > "${VIRTTEMP}.int"
                        mv -f "${VIRTTEMP}.int" "${VIRTFILE}"
                        ADDPURGE=`echo "${ADDFIND}" | cut -d" " -f2 | tr "\n" " " | sanitize_list`
                        for j in ${ADDPURGE}; do
                            grep -v "^${j}$" "${ACTVFILE}" > "${ACTVFILE}.int"
                            mv -f "${ACTVFILE}.int" "${ACTVFILE}"
                        done
                        echo "addauth ${ADDUSER} ${ADDFLAG} ${ADDSKEY} ${ADDMAIL}" >> "${AUTHTEMP}"
                        echo "${ADDUSER}@redeclipse.net ${ADDMAIL}" >> "${VIRTFILE}"
                        echo "${ADDUSER}" >> "${ACTVFILE}"
                    else
                        echo "email exists, skipping"
                        sed -e "s/~USERNAME~/${ADDUSER}/g;s/~USERMAIL~/${ADDMAIL}/g;s/~BOUNDARY~/$(head -c 64 /dev/urandom | shasum | cut -d' ' -f1)/g" "${UPDDIR}/mail/exists" | ${SENDMAIL} "${ADDMAIL}"
                    fi
                else
                    ADDFIND=`grep "^addauth ${ADDUSER} " "${AUTHTEMP}"`
                    if [ -n "${ADDFIND}" ]; then
                        q=1
                        r="${ADDUSER}"
                        while [ -n "${ADDFIND}" ]; do
                            ADDUSER="${r}${q}"
                            ADDFIND=`grep "^addauth ${ADDUSER} " "${AUTHTEMP}"`
                            q=$(( q + 1 ))
                        done
                        echo -n "renamed to: ${ADDUSER} "
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
                    echo "generated keys, adding user"
                    echo "addauth ${ADDUSER} ${ADDFLAG} ${ADDSKEY} ${ADDMAIL}" >> "${AUTHTEMP}"
                    sed -e "s/~USERNAME~/${ADDUSER}/g;s/~USERMAIL~/${ADDMAIL}/g;s/~USERKEY~/${ADDUKEY}/g;s/~BOUNDARY~/$(head -c 64 /dev/urandom | shasum | cut -d' ' -f1)/g" "${UPDDIR}/mail/reply" | ${SENDMAIL} "${ADDMAIL}"
                    ADDCOUNT=$(( ADDCOUNT + 1 ))
                fi
            fi
        done
    fi
    if [ "${ADDCOUNT}" -gt 0 ]; then
        echo "Added ${ADDCOUNT} user(s)..."
    fi
    rm -f "${ADDUFILE}"
fi

AUTHDIFF=`diff "${AUTHTEMP}" "${AUTHPREV}"`
if [ -n "${AUTHDIFF}" ]; then
    echo "Transmitting updates..."
    echo "${AUTHDIFF}"
    sort -b "${AUTHTEMP}" > "${AUTHFILE}"
    echo -e "put \"${AUTHFILE}\" \"${UPDAUTH}\"" > "${SFTPFILE}"
    ${SFTPCONN} -b "${SFTPFILE}" "${UPDHOST}" && ${RESERVER} "${UPDHOST}" "${RECMDS}" && cp -f "${AUTHFILE}" "${AUTHPREV}"
fi
rm -f "${AUTHTEMP}"

USERCOUNT=0
USERLIST=`grep "^addauth " "${AUTHFILE}" | sed -e "s/^\([^ ]*\) \([^ ]*\) \([^ ]*\) \([^ ]*\) \([^ ]*\).*$/\2 \5/"`
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
            echo "New Member: ${USERNAME} <${USERMAIL}>"
            echo "${USERNAME}" >> "${ACTVFILE}"
            echo "${USERMAIL}" >> "${MEMBFILE}"
            echo "${USERNAME}@redeclipse.net ${USERMAIL}" >> "${VIRTFILE}"
            USERCOUNT=$(( USERCOUNT + 1 ))
        fi
        CURLINE=$(( CURLINE + 1 ))
    done
    if [ "${USERCOUNT}" -gt 0 ]; then
        echo "Processing ${USERCOUNT} user(s)..."
        /usr/sbin/add_members -r "${MEMBFILE}" -w "n" "news"
    fi
fi

if [ "${DELCOUNT}" -gt 0 ] || [ "${PRGCOUNT}" -gt 0 ] || [ "${USERCOUNT}" -gt 0 ]; then
    /usr/sbin/postmap "${VIRTFILE}" && /usr/sbin/postfix reload
fi

rm -f "${LOCKFILE}"
chown -R root:www-data "${UPDDIR}" > /dev/null
chmod -R ug+rw "${UPDDIR}" > /dev/null
