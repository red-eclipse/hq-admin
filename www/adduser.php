<?php
    $pagename = "Add User";
    include_once("/var/www/src/header.php");
?>
<p>Below you can add users to the system. You can change a username or flags by entering an existing email.</p>
<?php
    $userflag = "";
    $usermail = $_GET['mail'] ?? "";
    $username = $_GET['user'] ?? "";
    $issubmit = $_GET['submit'] ?? "";
    if ($usermail != "" && $username != "") {
        $userflag = $_GET['flag'] ?? "";
        if ($userflag == "") $userflag = "u";
        echo "<h3>Processing</h3>";
        if (preg_match("/^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i", $usermail)) {
            echo "<p><tt>&nbsp;&nbsp;OK:</tt> Email <b>'" . $usermail . "'</b> is <b>VALID</b>.</p>";
            if (preg_match("/^[a-z][a-z0-9]+$/", $username)) {
                echo "<p><tt>&nbsp;&nbsp;OK:</tt> Username <b>'" . $username . "'</b> is <b>VALID</b>.</p>";
                echo "<p><tt>&nbsp;&nbsp;OK:</tt> Flag(s) <b>'" . $userflag . "'</b> are set.</p>";
                if ($issubmit != "") {
                    $execstr = shell_exec("echo '" . escapeshellcmd($usermail) . " " . escapeshellcmd($username) . " " . escapeshellcmd($userflag) ."' >> /var/lib/reauth/temp/adduser.cfg && echo 'OK, submitted user addition successfully.' || echo 'FAILED, try again later.'");
                    echo "<p><tt>&nbsp;RET:</tt> <b>" . $execstr . "</b></p>";
                } else {
?>
                    <form>
                        <p><input type="hidden" name="mail" value="<?php echo $usermail; ?>"></p>
                        <p><input type="hidden" name="user" value="<?php echo $username; ?>"></p>
                        <p><input type="hidden" name="flag" value="<?php echo $userflag; ?>"></p>
                        <p><tt>CONF:</tt> <input type="submit" name="submit" value="Confirm User Addition"></p>
                    </form>
<?php           }
            } else {
                echo "<p><tt>FAIL:</tt> Username <b>" . $username . "</b> is <b>INVALID</b>.</p>";
            }
        } else {
            echo "<p><tt>FAIL:</tt> Email <b>" . $usermail . "</b> is <b>INVALID</b>.</p>";
        }
    } else {
        echo "<p><tt>NOTE:</tt> Both <b>MAIL</b> and <b>USER</b> are required.</p>";
    }
?>
<h3>Form</h3>
<form>
    <p><tt>MAIL:</tt> <input type="text" name="mail" value="<?php echo $usermail; ?>"></p>
    <p><tt>USER:</tt> <input type="text" name="user" value="<?php echo $username; ?>"></p>
    <p><tt>FLAG:</tt> <input type="text" name="flag" value="<?php echo $userflag; ?>"></p>
    <p><tt>DONE:</tt> <input type="submit" value="Check Submission"></p>
</form>
<?php include_once("/var/www/src/footer.php"); ?>
