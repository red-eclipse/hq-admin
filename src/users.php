<?php
    $userlevels['u'] = "Player";
    $userlevels['s'] = "Supporter";
    $userlevels['m'] = "Moderator";
    $userlevels['o'] = "Operator";
    $userlevels['a'] = "Administrator";
    $userlevels['d'] = "Developer";
    $userlevels['c'] = "Founder";
    function load_users() {
        global $site;
        $handle = fopen($site['authcfg'], "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (preg_match("/^addauth ([^ ]*) ([^ ]*) ([^ ]*) ([^ ]*)$/", $line)) {
                    $user = explode(" ", trim($line));
                    if ($user) $users[] = $user;
                }
            }
            return $users;
        }
        return NULL;
    }
    function find_user_by_name($name) {
        if (preg_match("/^[a-z][a-z0-9]+$/", $name)) {
            global $site;
            $handle = fopen($site['authcfg'], "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (preg_match("/^addauth " . $name . " ([^ ]*) ([^ ]*) ([^ ]*)$/", $line)) {
                        return explode(" ", trim($line));
                    }
                }
            }
        }
        return NULL;
    }
    function find_user_by_mail($name) {
        if (preg_match("/^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i", $name)) {
            global $site;
            $handle = fopen($site['authcfg'], "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (preg_match("/^addauth ([^ ]*) ([^ ]*) ([^ ]*) " . $name . "$/", $line)) {
                        return explode(" ", trim($line));
                    }
                }
            }
        }
        return NULL;
    }
?>
