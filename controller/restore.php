<?php
/**
 * Created by PhpStorm.
 * Author: Joseph Gundo
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use Illuminate\Database\Capsule\Manager as Capsule;

//Restore Custom Fields to Default
$relation_table = Capsule::table("mod_ProductCreditRelation")
    ->get();


foreach ($relation_table as $item) {

    //Get Custom Field IDs
    $task_credit = Capsule::table("tblcustomfields")
        ->where("fieldname", "Task Credits")
        ->where("relid", $item->pid)
        ->first();

    $assistant = Capsule::table("tblcustomfields")
        ->where("fieldname", "Assistant")
        ->where("relid", $item->pid)
        ->first();

    //Get Department Name
    $depid = explode(',', $item->depid);
    $department_info = Capsule::table("tblticketdepartments")
        ->where("id", $depid[0])
        ->first();

    $postData = array(
        'serviceid' => $item->serviceid,
        'customfields' => base64_encode(serialize(array(
            $task_credit->id => $item->remaining,
            $assistant->id => $department_info->name,
        ))),
    );

    localAPI("UpdateClientProduct", $postData);
}

header("Location: addonmodules.php?module=task_credits&restoremessage=success");
exit();