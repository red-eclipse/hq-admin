<?php
    $site['pagename'] = "List Users";
    include_once("../src/auth.php");
?>
<p>Below is a list of all users in the system.</p>
<table>
    <thead><tr><th>Index</th><th>Handle</th><th>Level</th><th>Email</th><th>Actions</th></tr></thead>
    <tbody>
        <?php
            $userlist = load_users();
            $index = 1;
            foreach ($userlist as $user) {
                echo "<tr>";
                echo "<td>" . $index . "</td>";
                echo "<td><b>" . $user[1]. "</b></td>";
                echo "<td>" . $userlevels[$user[2]] . "</td>";
                echo "<td><a href=\"mailto:" . $user[4] . "\">" . $user[4]. "</td>";
                echo "<td>";
                echo "<a href=\"adduser.php?mail=" . $user[4] . "&user=" . $user[1] . "&level=" . $user[2] . "\">Modify User</a>";
                echo " | <a href=\"deluser.php?mail=" . $user[4] . "\">Delete User</a>";
                echo "</td>";
                echo "</tr>\n";
                $index = $index + 1;
            }
        ?>
    </tbody>
</table>
<?php include_once("footer.php"); ?>
