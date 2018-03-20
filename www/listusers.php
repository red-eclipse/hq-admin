<?php
    $site['pagename'] = "List Users";
    include_once("../src/auth.php");
?>
<p>Below is a list of all users in the system.</p>
<table>
    <thead><tr><th>Index</th><th>Handle</th><th>Level</th><th class="hide-small">Email</th><th>Actions</th></tr></thead>
    <tbody>
        <?php
            $users = user_load();
            $index = 1;
            foreach ($users as $userkey => $userinfo) {
                echo "<tr>";
                echo "<td>" . $index . "</td>";
                echo "<td><b>" . $userinfo['user'] . "</b></td>";
                echo "<td><b>" . user_icon($userinfo['level']) . "</b></td>";
                echo "<td class=\"hide-small\"><a href=\"mailto:" . $userinfo['email'] . "\">" . $userinfo['email']. "</td>";
                echo "<td><b>";
                //form_adduser(false, false);
                //form_deluser(false, false);
                echo "<a href=\"adduser.php?mail=" . $userinfo['email'] . "&user=" . $userinfo['user'] . "\" class=\"nowrap-text\">Modify User</a>";
                echo " <a href=\"deluser.php?mail=" . $userinfo['email'] . "\" class=\"nowrap-text\">Delete User</a>";
                echo "</b></td>";
                echo "</tr>\n";
                $index = $index + 1;
            }
        ?>
    </tbody>
</table>
<?php include_once("footer.php"); ?>
