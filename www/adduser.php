<?php
    $pagename = "Add User";
    include_once("/var/www/src/header.php");
?>
<p>Below you can add users to the system.</p>
<?php
    $usermail = $_GET['mail'] ?? "";
    $username = $_GET['user'] ?? "";
    if ($usermail != "" && $username != "") {
        if (preg_match("/^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i", $usermail)) {
            echo "<p><tt>&nbsp;&nbsp;OK:</tt> Email <b>" . $usermail . "</b> is <b>VALID</b>.</p>";
            if (preg_match("/^[a-z][a-z0-9]+$/", $username)) {
                echo "<p><tt>&nbsp;&nbsp;OK:</tt> Username <b>" . $username . "</b> is <b>VALID</b>.</p>";
                $execstr = shell_exec("echo '" . escapeshellcmd($usermail) . " " . escapeshellcmd($username) . "' >> /var/lib/reauth/temp/adduser.cfg && echo 'OK, submitted user addition successfully.' || echo 'FAILED, try again later.'");
                echo "<p><tt>&nbsp;RET:</tt> <b>" . $execstr . "</b></p>";
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
<form>
    <p><tt>MAIL:</tt> <input type="text" name="mail" value="<?php echo $usermail; ?>"></p>
    <p><tt>USER:</tt> <input type="text" name="user" value="<?php echo $username; ?>"></p>
    <p><tt>DONE:</tt> <input type="submit" value="Schedule User Addition"></p>
</form>
<?php include_once("/var/www/src/footer.php"); ?>
