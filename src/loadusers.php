<?php
    $users = array();
    $handle = fopen("/var/lib/reauth/auth.cfg", "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            if (preg_match("/^addauth ([^ ]*) ([^ ]*) ([^ ]*) ([^ ]*)$/", $line)) {
                $user = explode(" ", trim($line));
                if ($user) $users[] = $user;
            }
        }
    }
?>

