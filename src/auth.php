<?php
    $site['authcfg'] = "/var/lib/reauth/auth.cfg";
    set_include_path(get_include_path() . ":/var/lib/reauth/src:/var/www/src");
    include_once("header.php");
    include_once("users.php");
    include_once("forms.php");
?>
