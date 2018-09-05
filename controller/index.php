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
$ticket_credits = Capsule::table("mod_ticket_credits as credit")
    ->leftJoin("tblproducts as pro", "pro.id", "=", "credit.pid")
    ->leftJoin("tblticketdepartments as dept", "dept.id", "=", "credit.depid")
    ->select("credit.*", "pro.name as productname", "dept.name as department")
    ->get();

// Get Support Departments
$departments = localAPI("getSupportDepartments", [
    "ignore_dept_assignments" => true
]);

// Get Products
$products = localapi("getProducts", [
    ""
]);

//Create Smarty Variable
$smarty = new Smarty();

//Assign Smarty Variable
$smarty->assign('ticket_credits', json_decode(json_encode($ticket_credits), true));

//Load tpl
$smarty->display(__DIR__ . '/../views/index.tpl');