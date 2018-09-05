<?php
/**
 * Created by PhpStorm.
 * Author: Joseph Gundo
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use Illuminate\Database\Capsule\Manager as Capsule;

$line_id = $_GET['lineid'];

//Create Smarty Variable
$smarty = new Smarty();

//Save Posted Form
if (isset($_POST['pid']) && isset($_POST['depid'])) {

    //Update Database Line
    $db_result = Capsule::table("mod_ticket_credits")
        ->where("id", $line_id)
        ->update([
            "pid" => $_POST['pid'],
            "depid" => $_POST['depid'],
            "credits" => $_POST['credits'],
        ]);

    //Assign Message Variable
    $smarty->assign('db_result', $db_result);
}

// Get Ticket Credit Info
$ticket_credit = (array) Capsule::table("mod_ticket_credits")
    ->where("id", $line_id)
    ->first();

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

//Assign Smarty Variable
$smarty->assign('products', $products['products']['product']);
$smarty->assign('ticket_credits', $ticket_credits_pids);
$smarty->assign('ticket_credit', $ticket_credit);
$smarty->assign('departments', $departments['departments']['department']);

//Load tpl
$smarty->display(__DIR__ . '/../views/editline.tpl');