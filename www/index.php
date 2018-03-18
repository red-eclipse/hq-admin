<?php
    $pagename = "Admin Area";
    include_once("/var/www/src/header.php");
?>
<p>Welcome to the Red Eclipse Administrator interface. Below are your options:</p>
<p>
    <ul>
        <li><b><a href="listusers.php">List Users</a></b></li>
        <li><b><a href="adduser.php">Add User</a></b></li>
        <li><b><a href="deluser.php">Delete User</a></b></li>
    </ul>
</p>
<?php include_once("/var/www/src/footer.php"); ?>
