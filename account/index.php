<?php
    include_once("/var/www/src/common.php");
    $site['pagename'] = "Update Details";
    include_once("../src/auth.php");
    $curuser['email'] = $_GET['mail'] ?? "";
    $curuser['user'] = $_GET['user'] ?? "";
    $curuser['sid'] = $_GET['sid'] ?? "";
    $userkey = $_GET['key'] ?? "";
    $issubmit = $_GET['submit'] ?? "";
    $userinfo = user_byemail($curuser['email']);
    $userbyname = user_byname($curuser['user']);
    if (!is_null($userinfo)) {
        if ($curuser['user'] == "") $curuser['user'] = $userinfo['user'];
    } elseif (!is_null($userbyname)) { 
        if ($curuser['email'] == "") $curuser['email'] = $userbyname['email'];
        if(is_null($userinfo)) $userinfo = $userbyname;
    }
    if ($curuser['sid'] == "") { $curuser['sid'] = "0"; }
    form_update(false, true, "Input", $curuser, $userkey);
    if ($curuser['email'] != "" && $curuser['user'] != "") {
        echo "<h2>Output</h2>";
        if (!stristr($curuser['email'], "redeclipse.net") && preg_match("/^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i", $curuser['email'])) {
            echo "<p><tt>&nbsp;&nbsp;&nbsp;&nbsp;OK:</tt> Email <b><tt>'" . $curuser['email'] . "'</tt></b> is <b>valid</b>.</p>";
            if (preg_match("/^[a-z][a-z0-9]+$/", $curuser['user'])) {
                if (is_null($userbyname) || $userbyname['email'] == $curuser['email']) {
                    if (!is_null($userinfo)) {
                        echo "<p><tt>&nbsp;&nbsp;USER:</tt> Found existing user <b><tt>'" . $userinfo['user'] . "'</tt></b> in the database.</p>";
                        if ($curuser['user'] == $userinfo['user']) {
                            echo "<p><tt>&nbsp;&nbsp;&nbsp;&nbsp;OK:</tt> Username <b><tt>'" . $curuser['user'] . "'</tt></b> is <b>valid</b> and <b>matches</b> the existing entry.</p>";
                        } else {
                            echo "<p><tt>&nbsp;CHECK:</tt> Username <b><tt>'" . $curuser['user'] . "'</tt></b> is <b>valid</b> but <b>differs</b> from <b><tt>'" . $userinfo['user'] . "'</tt></b> in the existing entry.</p>";
                        }
                    }
                    else {
                        echo "<p><tt>&nbsp;&nbsp;&nbsp;&nbsp;OK:</tt> Username <b><tt>'" . $curuser['user'] . "'</tt></b> is <b>valid</b> and <b>has no</b> existing entry.</p>";
                    }
                    echo "<p><tt>&nbsp;&nbsp;&nbsp;&nbsp;OK:</tt> SteamID <b><tt>'" . $curuser['sid'] . "'</tt></b> is <b>set</b></p>";
                    if(user_auth($userinfo, $userkey)) {
                        if ($issubmit != "") {
                            if(is_null($userinfo) || $curuser['user'] != $userinfo['user'] || $curuser['sid'] != $userinfo['sid']) {
                                $execstr = shell_exec("echo '" . escapeshellcmd($userinfo['email']) . " " . escapeshellcmd($curuser['user']) . " " . escapeshellcmd($userinfo['level']) ." " . escapeshellcmd($curuser['sid']) . "' >> /var/lib/reauth/temp/adduser.cfg && echo 'OK, submitted updates successfully.' || echo 'FAILED, try again later.'");
                                echo "<p><tt>&nbsp;&nbsp;&nbsp;RET:</tt> <b>" . $execstr . "</b></p>";
                            } else {
                                echo "<p><tt>&nbsp;ERROR:</tt> Requested details <b>already match existing entry</b>.</p>";
                            }
                        } else {
                            if(is_null($userinfo) || $curuser['user'] != $userinfo['user'] || $curuser['sid'] != $userinfo['sid']) {
                                form_update(true, false, "Confirm Details", $curuser, $userkey);
                            } else {
                                echo "<p><tt>&nbsp;&nbsp;NOTE:</tt> Requested details <b>already match existing entry</b>, edit above to make changes.</p>";
                            }
                        }
                    } else {
                        echo "<p><tt>&nbsp;ERROR:</tt> User key <b>doesn't match</b> the one on record.</p>";
                    }
                } else {
                    echo "<p><tt>&nbsp;&nbsp;FAIL:</tt> The user name <b><tt>'" . $curuser['user'] . "'</tt></b> is <b>in use by another player</b>.</p>";
                }
            } else {
                echo "<p><tt>&nbsp;&nbsp;FAIL:</tt> Username <b><tt>'" . $curuser['user'] . "'</tt></b> is <b>invalid</b>.</p>";
            }
        } else {
            echo "<p><tt>&nbsp;&nbsp;FAIL:</tt> Email <b><tt>'" . $curuser['email'] . "'</tt></b> is <b>invalid</b>.</p>";
        }
    } else {
        echo "<p><tt>&nbsp;&nbsp;NOTE:</tt> The paramaters <b><tt>EMAIL</tt></b> and <b><tt>USER</tt></b> are required.</p>";
    }
    include_once("footer.php");
?>
