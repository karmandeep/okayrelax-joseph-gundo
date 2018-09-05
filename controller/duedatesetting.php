<?php
/**
 * Created by PhpStorm.
 * Author: Joseph Gundo
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use Illuminate\Database\Capsule\Manager as Capsule;

//Create Smarty Variable
$smarty = new Smarty();

//Save Posted Form
if (isset($_POST['priority'])) {

    $save_arr = array(
        "priority" => $_POST['priority'],
        "customfields" => $_POST['customfields']
    );

    //Get Settings
    $settings = Capsule::table('tblconfiguration')
        ->where('setting', 'Task_Credit_Default_DueDate')
        ->first();


    if (isset($settings->setting)) {

        Capsule::table('tblconfiguration')
            ->where('setting', $settings->setting)
            ->update([
                'value' => json_encode($save_arr),
                'updated_at' => date("Y-m-d H:i:s")
            ]);
    } else {

        Capsule::table('tblconfiguration')
            ->insert([
                'setting' => 'Task_Credit_Default_DueDate',
                'value' => json_encode($save_arr),
                'created_at' => date("Y-m-d H:i:s")
            ]);
    }

    //Assign Message Variable
    $smarty->assign('db_result', $db_result);
}

//Get Settings
$settings = Capsule::table('tblconfiguration')
    ->where('setting', 'Task_Credit_Default_DueDate')
    ->first();

// Get Support Departments
$departments = localAPI("getSupportDepartments", [
    "ignore_dept_assignments" => true
]);

$departments = $departments['departments']['department'];

foreach ($departments as $department_key => $department) {

    $departments[$department_key]['customfields'] = Capsule::table("tblcustomfields")
        ->where("type", "support")
        ->where("relid", $department['id'])
        ->get();
}

$settings = json_decode($settings->value, true);

//Assign Smarty Variable
$smarty->assign('settings', $settings);
$smarty->assign('departments', json_decode(json_encode($departments), true));

//Load tpl
$smarty->display(__DIR__ . '/../views/duedatesetting.tpl');