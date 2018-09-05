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
if (isset($_POST['current_depid']) && isset($_POST['new_depid'])) {

    $relation_list = Capsule::table("mod_ProductCreditRelation")
        ->get();

    foreach ($relation_list as $item){

        $departments = explode(',', $item->depid);

        if ($departments[0] == $_POST['current_depid']) {

            $department = $_POST['new_depid'] . "," . $departments[1] . "," . $departments[2]; // department ids

            $new_department_info = Capsule::table("tblticketdepartments")
                ->where("id", $_POST["new_depid"])
                ->first();

            Capsule::table("mod_ProductCreditRelation")
                ->where("id", $item->id)
                ->update([
                    "depid" => $department
                ]);

            $product_custom_field = Capsule::table("tblhosting as host")
                ->leftJoin("tblcustomfields as cust", "cust.relid", "=", "host.packageid")
                ->where("host.id", $item->serviceid)
                ->where("cust.type", "product")
                ->where("cust.fieldname", "Assistant")
                ->select("cust.id")
                ->first();

            $db_result = Capsule::table("tblcustomfieldsvalues")
                ->where("fieldid", $product_custom_field->id)
                ->where("relid", $item->serviceid)
                ->update([
                    "value" => $new_department_info->name
                ]);

            $client_tickets = localapi("gettickets", [
                'clientid' => $item->userid,
                'deptid' => $_POST['current_depid']
            ]);

            foreach ($client_tickets['tickets']['ticket'] as $ticket){

                localapi("updateticket", [
                    'ticketid' => $ticket->id,
                    'deptid' => $_POST['current_depid']
                ]);
            }
        }
    }

    //Assign Message Variable
    $smarty->assign('db_result', $db_result);
}

// Get Support Departments
$departments = localAPI("getSupportDepartments", [
    "ignore_dept_assignments" => true
]);

//Assign Smarty Variable
$smarty->assign('ticket_credits', $ticket_credits_pids);
$smarty->assign('ticket_credit', $ticket_credit);
$smarty->assign('departments', $departments['departments']['department']);

//Load tpl
$smarty->display(__DIR__ . '/../views/settings.tpl');