<?php
    $site['pagename'] = "Add User";
    include_once("../src/auth.php");
    $userlevel = "";
    $usermail = $_GET['mail'] ?? "";
    $username = $_GET['user'] ?? "";
    $userlevel = $_GET['level'] ?? "";
    $issubmit = $_GET['submit'] ?? "";
    $userinfo = find_user_by_mail($usermail);
    if ($userlevel == "") {
        if(is_null($userinfo)) {
            $userlevel = "u";
        } else {
            $userlevel = $userinfo[2];
        }
    }
    form_adduser(false, true, "Input", $usermail, $username, $userlevel);
    if ($usermail != "" && $username != "") {
        echo "<h2>Output</h2>";
        if (preg_match("/^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i", $usermail)) {
            echo "<p><tt>&nbsp;&nbsp;&nbsp;&nbsp;OK:</tt> Email <b><tt>'" . $usermail . "'</tt></b> is <b>valid</b>.</p>";
            if (preg_match("/^[a-z][a-z0-9]+$/", $username)) {
                $userbyname = find_user_by_name($username);
                if (is_null($userbyname) || $userbyname[4] == $usermail) {
                    if (preg_match("/^[usmoadc]$/", $userlevel)) {
                        if (!is_null($userinfo)) {
                            echo "<p><tt>&nbsp;&nbsp;USER:</tt> Found existing user <b><tt>'" . $userinfo[1] . "'</tt></b> with level <b><tt>'" . $userinfo[2] . "'</tt></b> matching <b><tt>'" . $usermail . "'</tt></b> in the database.</p>";
                            if ($username == $userinfo[1]) {
                                echo "<p><tt>&nbsp;&nbsp;&nbsp;&nbsp;OK:</tt> Username <b><tt>'" . $username . "'</tt></b> is <b>valid</b> and <b>matches</b> the existing entry.</p>";
                            } else {
                                echo "<p><tt>&nbsp;CHECK:</tt> Username <b><tt>'" . $username . "'</tt></b> is <b>valid</b> but <b>differs</b> from <b><tt>'" . $userinfo[1] . "'</tt></b> in the existing entry.</p>";
                            }
                            if ($userlevel == $userinfo[2]) {
                                echo "<p><tt>&nbsp;&nbsp;&nbsp;&nbsp;OK:</tt> Level <b><tt>'" . $userlevels[$userlevel] . "'</tt></b> is <b>valid</b> and <b>matches</b> the existing entry.</p>";
                            } else {
                                echo "<p><tt>&nbsp;CHECK:</tt> Level <b><tt>'" . $userlevels[$userlevel] . "'</tt></b> is <b>different</b> to <b><tt>'" . $userlevels[$userinfo[2]] . "'</tt></b> in the existing entry.</p>";
                            }
                        }
                        else {
                            echo "<p><tt>&nbsp;&nbsp;&nbsp;&nbsp;OK:</tt> Username <b><tt>'" . $username . "'</tt></b> is <b>valid</b> and <b>has no</b> existing entry.</p>";
                            echo "<p><tt>&nbsp;&nbsp;&nbsp;&nbsp;OK:</tt> Level <b><tt>'" . $userlevels[$userlevel] . "'</tt></b> is <b>set</b></p>";
                        }
                        if ($issubmit != "") {
                            if(is_null($userinfo) || $username != $userinfo[1] || $userlevel != $userinfo[2]) {
                                $execstr = shell_exec("echo '" . escapeshellcmd($usermail) . " " . escapeshellcmd($username) . " " . escapeshellcmd($userlevel) ."' >> /var/lib/reauth/temp/adduser.cfg && echo 'OK, submitted user successfully.' || echo 'FAILED, try again later.'");
                                echo "<p><tt>&nbsp;&nbsp;&nbsp;RET:</tt> <b>" . $execstr . "</b></p>";
                            } else {
                                echo "<p><tt>&nbsp;ERROR:</tt> Requested details <b>already match existing entry</b>.</p>";
                            }
                        } else {
                            if(is_null($userinfo) || $username != $userinfo[1] || $userlevel != $userinfo[2]) {
                                form_adduser(true, false, "Confirm Details", $usermail, $username, $userlevel);
                            } else {
                                echo "<p><tt>&nbsp;&nbsp;NOTE:</tt> Requested details <b>already match existing entry</b>, edit above to make changes.</p>";
                            }
                        }
                    } else {
                        echo "<p><tt>&nbsp;&nbsp;FAIL:</tt> Level <b><tt>'" . $userlevel . "'</tt></b> is <b>invalid</b>.</p>";
                    }
                } else {
                    echo "<p><tt>&nbsp;&nbsp;FAIL:</tt> The user name <b><tt>'" . $username . "'</tt></b> is <b>in use by another player</b>.</p>";
                }
            } else {
                echo "<p><tt>&nbsp;&nbsp;FAIL:</tt> Username <b><tt>'" . $username . "'</tt></b> is <b>invalid</b>.</p>";
            }
        } else {
            echo "<p><tt>&nbsp;&nbsp;FAIL:</tt> Email <b><tt>'" . $usermail . "'</tt></b> is <b>invalid</b>.</p>";
        }
    } else {
        echo "<p><tt>&nbsp;&nbsp;NOTE:</tt> The paramaters <b><tt>EMAIL</tt></b>, <b><tt>USER</tt></b>, and <b><tt>LEVEL</tt></b> are all required.</p>";
    }
    include_once("footer.php");
?>
