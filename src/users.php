<?php
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
        global $site;
        $handle = fopen($site['authcfg'], "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (preg_match("/^addauth " . $name . " ([^ ]*) ([^ ]*) ([^ ]*)$/", $line)) {
                    return explode(" ", trim($line));
                }
            }
        }
        return NULL;
    }
    function find_user_by_mail($name) {
        global $site;
        $handle = fopen($site['authcfg'], "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (preg_match("/^addauth ([^ ]*) ([^ ]*) ([^ ]*) " . $name . "$/", $line)) {
                    return explode(" ", trim($line));
                }
            }
        }
        return NULL;
    }
?>
