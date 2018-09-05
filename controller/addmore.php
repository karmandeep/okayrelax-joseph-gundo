<?php
/**
 * Created by PhpStorm.
 * Author: Joseph Gundo
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use Illuminate\Database\Capsule\Manager as Capsule;

// Get Ticket Credits
$ticket_credits = Capsule::table("mod_ticket_credits")
    ->select("pid")
    ->get();

$ticket_credits_pids = [];
foreach ($ticket_credits as $pid) {

    $ticket_credits_pids[] = $pid->pid;
}

// Get Support Departments
$departments = localAPI("getSupportDepartments", [
    "ignore_dept_assignments" => true
]);

// Get Products
$products = localAPI("getProducts", []);


//Create Smarty Variable
$smarty = new Smarty();

//Save Posted Form
if (isset($_POST['pid']) && isset($_POST['depid'])) {

    //Insert to database
    $db_result = Capsule::table("mod_ticket_credits")
        ->insert([
            "pid" => $_POST['pid'],
            "depid" => $_POST['depid'],
            "credits" => $_POST['credits'],
        ]);


    $get_assistant_field = Capsule::table("tblcustomfields")
        ->where('relid', $_POST['pid'])
        ->where('fieldname', "Assistant")
        ->where('type', "product")
        ->first();

    if(!isset($get_assistant_field->id)) {

        //Add Custom Fields to Product
        Capsule::table("tblcustomfields")
            ->insert([
                "relid" => $_POST['pid'],
                "fieldname" => "Assistant",
                "type" => "product",
                "fieldtype" => "text",
                "adminonly" => "on",
            ]);

        Capsule::table("tblcustomfields")
            ->insert([
                "relid" => $_POST['pid'],
                "fieldname" => "Task Credits",
                "type" => "product",
                "fieldtype" => "text",
                "adminonly" => "on",
            ]);
    }
    //Assign Message Variable
    $smarty->assign('db_result', $db_result);
}

//Assign Smarty Variable
$smarty->assign('products', $products['products']['product']);
$smarty->assign('ticket_credits', $ticket_credits_pids);
$smarty->assign('departments', $departments['departments']['department']);

//Load tpl
$smarty->display(__DIR__ . '/../views/addmore.tpl');