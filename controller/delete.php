<?php
/**
 * Created by PhpStorm.
 * Author: Joseph Gundo
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use Illuminate\Database\Capsule\Manager as Capsule;

//Delete Line

if (isset( $_GET['lineid']) && $_GET['lineid'] > 0){

    Capsule::table("mod_ticket_credits")
        ->where("id", $_GET['lineid'])
        ->delete();

    header("Location: addonmodules.php?module=task_credits");
    exit();
}