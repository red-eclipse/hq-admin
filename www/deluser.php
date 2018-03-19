<?php
    $pagename = "Delete User";
    include_once("/var/www/src/header.php");
    include_once("../src/config.php");
    include_once("../src/users.php");
?>
<p>Below you can delete users from the system.</p>
<?php
    $usermail = $_GET['mail'] ?? "";
    $issubmit = $_GET['submit'] ?? "";
    if ($usermail != "") {
        echo "<h3>Processing</h3>";
        if (preg_match("/^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i", $usermail)) {
            echo "<p><tt>&nbsp;&nbsp;&nbsp;&nbsp;OK:</tt> Email <b><tt>'" . $usermail . "'</tt></b> is <b>valid</b>.</p>";
            $userinfo = find_user_by_mail($usermail);
            if (!is_null($userinfo)) {
                echo "<p><tt>&nbsp;&nbsp;USER:</tt> Found existing user <b><tt>'" . $userinfo[1] . "'</tt></b> with flags <b><tt>'" . $userinfo[2] . "'</tt></b> in the database.</p>";
                if ($issubmit != "") {
                    $execstr = shell_exec("echo '" . escapeshellcmd($usermail) . "' >> /var/lib/reauth/temp/deluser.cfg && echo 'OK, submitted user deletion successfully.' || echo 'FAILED, try again later.'");
                    echo "<p><tt>&nbsp;&nbsp;&nbsp;RET:</tt> <b>" . $execstr . "</b></p>";
                } else {
?>
                    <form>
                        <input type="hidden" name="mail" value="<?php echo $usermail; ?>">
                        <p><tt>&nbsp;CNFRM:</tt> <input type="submit" name="submit" value="Confirm Submission"></p>
                    </form>
<?php           }
            } else {
                echo "<p><tt>&nbsp;&nbsp;FAIL:</tt> User matching <b><tt>'" . $usermail . "'</tt></b> was <b>not found</b>.</p>";
            }
        } else {
            echo "<p><tt>&nbsp;&nbsp;FAIL:</tt> Email <b><tt>'" . $usermail . "'</tt></b> is <b>invalid</b>.</p>";
        }
    } else {
        echo "<p><tt>&nbsp;&nbsp;NOTE:</tt> The <b>MAIL</b> parameter is required.</p>";
    }
?>
<h3>Form Input</h3>
<form>
    <p><tt>&nbsp;EMAIL:</tt> <input type="text" name="mail" value="<?php echo $usermail; ?>"></p>
    <p><tt>&nbsp;&nbsp;DONE:</tt> <input type="submit" value="Check Submission"></p>
</form>
<?php include_once("/var/www/src/footer.php"); ?>
