<?php
    $pagename = "Add User";
    include_once("/var/www/src/header.php");
?>
<p>Below you can delete users from the system.</p>
<?php
    $usermail = $_GET['mail'] ?? "";
    if ($usermail != "") {
        if (preg_match("/^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i", $usermail)) {
            echo "<p><tt>&nbsp;&nbsp;OK:</tt> Email <b>" . $usermail . "</b> is <b>VALID</b>.</p>";
            $execstr = shell_exec("echo '" . escapeshellcmd($usermail) . "' >> /var/lib/reauth/temp/deluser.cfg && echo 'OK, submitted user deletion successfully.' || echo 'FAILED, try again later.'");
            echo "<p><tt>&nbsp;RET:</tt> <b>" . $execstr . "</b></p>";
        } else {
            echo "<p><tt>FAIL:</tt> Email <b>" . $usermail . "</b> is <b>INVALID</b>.</p>";
        }
    } else {
        echo "<p><tt>NOTE:</tt> The <b>MAIL</b> parameter is required.</p>";
    }
?>
<form>
    <p><tt>MAIL:</tt> <input type="text" name="mail" value="<?php echo $usermail; ?>"></p>
    <p><tt>DONE:</tt> <input type="submit" value="Schedule User Deletion"></p>
</form>
<?php include_once("/var/www/src/footer.php"); ?>
