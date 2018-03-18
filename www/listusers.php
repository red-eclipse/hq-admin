<?php
    $pagename = "List Users";
    include_once("/var/www/src/header.php");
?>
<p>Below is a list of all users in the system.</p>
<?php include_once("../src/loadusers.php") ?>
<table>
    <thead><tr><th>Index</th><th>Handle</th><th>Flags</th><th>Email</th><th>Actions</th></tr></thead>
    <tbody>
        <?php
            $index = 1;
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . $index . "</td>";
                echo "<td><b>" . $user[1]. "</b></td>";
                echo "<td>" . $user[2]. "</td>";
                echo "<td><a href=\"mailto:" . $user[4] . "\">" . $user[4]. "</td>";
                echo "<td>";
                echo "<a href=\"adduser.php?mail=" . $user[4] ."&user=" . $user[1] ."\">Modify User</a>";
                echo " | <a href=\"deluser.php?mail=" . $user[4] ."\">Delete User</a>";
                echo "</td>";
                echo "</tr>\n";
                $index = $index + 1;
            }
        ?>
    </tbody>
</table>
<?php include_once("/var/www/src/footer.php"); ?>
