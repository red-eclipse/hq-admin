<?php
    $pagename = "List Users";
    include_once("/var/www/src/header.php");
    include_once("../src/config.php");
    include_once("../src/users.php");
?>
<p>Below is a list of all users in the system.</p>
<table>
    <thead><tr><th>Index</th><th>Handle</th><th>Flags</th><th>Email</th><th>Actions</th></tr></thead>
    <tbody>
        <?php
            $userlist = load_users();
            $index = 1;
            foreach ($userlist as $user) {
                echo "<tr>";
                echo "<td>" . $index . "</td>";
                echo "<td><b>" . $user[1]. "</b></td>";
                echo "<td><tt>" . $user[2]. "</tt></td>";
                echo "<td><a href=\"mailto:" . $user[4] . "\">" . $user[4]. "</td>";
                echo "<td>";
                echo "<a href=\"adduser.php?mail=" . $user[4] . "&user=" . $user[1] . "&flag=" . $user[2] . "\">Modify User</a>";
                echo " | <a href=\"deluser.php?mail=" . $user[4] . "\">Delete User</a>";
                echo "</td>";
                echo "</tr>\n";
                $index = $index + 1;
            }
        ?>
    </tbody>
</table>
<?php include_once("/var/www/src/footer.php"); ?>
