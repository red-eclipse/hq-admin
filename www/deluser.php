<?php
    $site['pagename'] = "Delete User";
    include_once("../src/auth.php");
    $usermail = $_GET['mail'] ?? "";
    $issubmit = $_GET['submit'] ?? "";
    form_deluser(false, true, "Input", $usermail);
    if ($usermail != "") {
        echo "<h2>Output</h2>";
        if (preg_match("/^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i", $usermail)) {
            echo "<p><tt>&nbsp;&nbsp;&nbsp;&nbsp;OK:</tt> Email <b><tt>'" . $usermail . "'</tt></b> is <b>valid</b>.</p>";
            $userinfo = find_user_by_mail($usermail);
            if (!is_null($userinfo)) {
                echo "<p><tt>&nbsp;&nbsp;USER:</tt> Found existing user <b><tt>'" . $userinfo[1] . "'</tt></b> with level <b><tt>'" . $userinfo[2] . "'</tt></b> in the database.</p>";
                if ($issubmit != "") {
                    $execstr = shell_exec("echo '" . escapeshellcmd($usermail) . "' >> /var/lib/reauth/temp/deluser.cfg && echo 'OK, submitted user deletion successfully.' || echo 'FAILED, try again later.'");
                    echo "<p><tt>&nbsp;&nbsp;&nbsp;RET:</tt> <b>" . $execstr . "</b></p>";
                } else {
                    form_deluser(true, false, "Confirm Details", $usermail);
                }
            } else {
                echo "<p><tt>&nbsp;&nbsp;FAIL:</tt> User matching <b><tt>'" . $usermail . "'</tt></b> was <b>not found</b> in the database.</p>";
            }
        } else {
            echo "<p><tt>&nbsp;&nbsp;FAIL:</tt> Email <b><tt>'" . $usermail . "'</tt></b> is <b>invalid</b>.</p>";
        }
    } else {
        echo "<p><tt>&nbsp;&nbsp;NOTE:</tt> The <b><tt>EMAIL</tt></b> parameter is required.</p>";
    }
    include_once("footer.php");
?>
