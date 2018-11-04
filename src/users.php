<?php
    $userattr = array (
        'u' => array('name' => "Player",        'lower' => "player",        'icon' => "https://raw.githubusercontent.com/red-eclipse/textures/master/privs/player.png"),
        's' => array('name' => "Supporter",     'lower' => "supporter",     'icon' => "https://raw.githubusercontent.com/red-eclipse/textures/master/privs/supporter.png"),
        'm' => array('name' => "Moderator",     'lower' => "moderator",     'icon' => "https://raw.githubusercontent.com/red-eclipse/textures/master/privs/moderator.png"),
        'o' => array('name' => "Operator",      'lower' => "operator",      'icon' => "https://raw.githubusercontent.com/red-eclipse/textures/master/privs/operator.png"),
        'a' => array('name' => "Administrator", 'lower' => "administrator", 'icon' => "https://raw.githubusercontent.com/red-eclipse/textures/master/privs/administrator.png"),
        'd' => array('name' => "Developer",     'lower' => "developer",     'icon' => "https://raw.githubusercontent.com/red-eclipse/textures/master/privs/developer.png"),
        'c' => array('name' => "Founder",       'lower' => "founder",       'icon' => "https://raw.githubusercontent.com/red-eclipse/textures/master/privs/founder.png"),
    );
    $users = NULL;
    $userattrs = array('addauth', 'user', 'level', 'key', 'email', 'sid');
    $userdefaults = array('addauth' => "", 'user' => "", 'level' => "", 'key' => "", 'email' => "", 'sid' => "");
    function user_text($level = 'u') {
        global $userattr;
        return "<span class=\"priv-" . $userattr[$level]['lower'] . "\">" . $userattr[$level]['name'] . "</span>";
    }
    function user_icon($level = 'u') {
        global $userattr;
        return "<span class=\"priv-" . $userattr[$level]['lower'] . "\"><img src=\"" . $userattr[$level]['icon'] . "\" class=\"img-text\" />" . $userattr[$level]['name'] . "</span>";;
    }
    function user_process($userinfo) {
        global $userattrs;
        if (!is_null($userinfo) && $userinfo != "") {
            $user = NULL;
            foreach ($userinfo as $userkey => $useritem) {
                if (isset($userattrs[$userkey])) {
                    $user[$userattrs[$userkey]] = $useritem;
                } else break;
            }
            return $user;
        }
        return NULL;
    }
    function user_load() {
        global $site, $users;
        $handle = fopen($site['authcfg'], "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (preg_match("/^addauth ([^ ]*) ([^ ]*) ([^ ]*) ([^ ]*) ([^ ]*)$/", $line)) {
                    $userload = user_process(explode(" ", trim($line)));
                    if (!is_null($userload)) $users[$userload['user']] = $userload;
                }
            }
            return $users;
        }
        return NULL;
    }
    function user_byname($name = "") {
        global $site, $users;
        if ($name != "" && preg_match("/^[a-z][a-z0-9]+$/", $name)) {
            if(!is_null($users) && !is_null($users[$name])) return $users[$name];
            $handle = fopen($site['authcfg'], "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (preg_match("/^addauth " . $name . " ([^ ]*) ([^ ]*) ([^ ]*) ([^ ]*)$/", $line)) {
                        $userload = user_process(explode(" ", trim($line)));
                        if (!is_null($userload)) $users[$userload['user']] = $userload;
                        return $users[$userload['user']];
                    }
                }
            }
        }
        return NULL;
    }
    function user_byemail($name = "") {
        global $site, $users;
        if ($name != "" && preg_match("/^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i", $name)) {
            $handle = fopen($site['authcfg'], "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (preg_match("/^addauth ([^ ]*) ([^ ]*) ([^ ]*) " . $name . " ([^ ]*)$/", $line)) {
                        $userload = user_process(explode(" ", trim($line)));
                        if (!is_null($userload)) $users[$userload['user']] = $userload;
                        return $users[$userload['user']];
                    }
                }
            }
        }
        return NULL;
    }
?>
