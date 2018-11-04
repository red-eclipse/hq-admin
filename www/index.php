<?php
    include_once("/var/www/src/common.php");
    $site['pagename'] = "Admin Area";
    include_once("../src/auth.php");
    echo "<p>Welcome to the Red Eclipse Administrator interface.</p>";
    echo "<p>You can <b><a href=\"listusers.php\">get a user list</a></b> or pick from the options below:</p>";
    form_adduser(false, false, "Add User");
    form_deluser(false, false, "Delete User");
    include_once("footer.php");
?>
