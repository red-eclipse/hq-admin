<?php
    $pagename = "Add User";
    include_once("/var/www/src/header.php");
    include_once("../src/config.php");
    include_once("../src/users.php");
?>
<p>Below you can add users to the system. You can change a username or flags by entering an existing email.</p>
<?php
    $userflag = "";
    $usermail = $_GET['mail'] ?? "";
    $username = $_GET['user'] ?? "";
    $issubmit = $_GET['submit'] ?? "";
    if ($usermail != "" && $username != "") {
        echo "<h3>Processing</h3>";
        if (preg_match("/^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i", $usermail)) {
            echo "<p><tt>&nbsp;&nbsp;&nbsp;&nbsp;OK:</tt> Email <b><tt>'" . $usermail . "'</tt></b> is <b>valid</b>.</p>";
            if (preg_match("/^[a-z][a-z0-9]+$/", $username)) {
                $userinfo = find_user_by_mail($usermail);
                $userflag = $_GET['flag'] ?? "";
                if (!is_null($userinfo)) {
                    echo "<p><tt>&nbsp;&nbsp;USER:</tt> Found existing user <b><tt>'" . $userinfo[1] . "'</tt></b> with flags <b><tt>'" . $userinfo[2] . "'</tt></b> in the database.</p>";
                    if ($userflag == "") $userflag = $userinfo[2];
                }
                elseif ($userflag == "") $userflag = "u";
                $usernamediff = !is_null($userinfo) && $username == $userinfo[1] ? "matches" : "differs from";
                echo "<p><tt>&nbsp;&nbsp;&nbsp;&nbsp;OK:</tt> Username <b><tt>'" . $username . "'</tt></b> is <b>valid</b> and <b>" . $usernamediff ."</b> existing entry.</p>";
                $userflagdiff = !is_null($userinfo) && $userflag == $userinfo[2] ? "matches" : "differs from";
                echo "<p><tt>&nbsp;&nbsp;&nbsp;&nbsp;OK:</tt> Flag(s) <b><tt>'" . $userflag . "'</tt></b> are set and <b>" . $userflagdiff ."</b> existing entry.</p>";
                if ($issubmit != "") {
                    if(is_null($userinfo) || $username != $userinfo[1] || $userflag != $userinfo[2]) {
                        $execstr = shell_exec("echo '" . escapeshellcmd($usermail) . " " . escapeshellcmd($username) . " " . escapeshellcmd($userflag) ."' >> /var/lib/reauth/temp/adduser.cfg && echo 'OK, submitted user successfully.' || echo 'FAILED, try again later.'");
                        echo "<p><tt>&nbsp;&nbsp;&nbsp;RET:</tt> <b>" . $execstr . "</b></p>";
                    } else {
                        echo "<p><tt>&nbsp;ERROR:</tt> Requested details <b>already match existing entry</b>.</p>";
                    }
                } else {
                    if(is_null($userinfo) || $username != $userinfo[1] || $userflag != $userinfo[2]) {
?>
                        <form>
                            <input type="hidden" name="mail" value="<?php echo $usermail; ?>">
                            <input type="hidden" name="user" value="<?php echo $username; ?>">
                            <input type="hidden" name="flag" value="<?php echo $userflag; ?>">
                            <p><tt>&nbsp;CNFRM:</tt> <input type="submit" name="submit" value="Confirm Submission"></p>
                        </form>
<?php               } else {
                        echo "<p><tt>&nbsp;&nbsp;NOTE:</tt> Requested details <b>already match existing entry</b>, edit below to change.</p>";
                    }
                }
            } else {
                echo "<p><tt>&nbsp;&nbsp;FAIL:</tt> Username <b>" . $username . "</b> is <b>invalid</b>.</p>";
            }
        } else {
            echo "<p><tt>&nbsp;&nbsp;FAIL:</tt> Email <b>" . $usermail . "</b> is <b>invalid</b>.</p>";
        }
    } else {
        echo "<p><tt>&nbsp;&nbsp;NOTE:</tt> Both <b>EMAIL</b> and <b>USER</b> are required.</p>";
    }
?>
<h3>Form Input</h3>
<form>
    <p><tt>&nbsp;EMAIL:</tt> <input type="text" name="mail" value="<?php echo $usermail; ?>"></p>
    <p><tt>&nbsp;&nbsp;USER:</tt> <input type="text" name="user" value="<?php echo $username; ?>"></p>
    <p><tt>&nbsp;FLAGS:</tt> <input type="text" name="flag" value="<?php echo $userflag; ?>"></p>
    <p><tt>&nbsp;&nbsp;DONE:</tt> <input type="submit" value="Check Submission"></p>
</form>
<?php include_once("/var/www/src/footer.php"); ?>
