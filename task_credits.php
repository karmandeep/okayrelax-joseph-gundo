<?php

use Illuminate\Database\Capsule\Manager as Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function task_credits_config()
{
    $configarray = array(
        "name" => "Task Credits",
        "description" => "This module is to set department and credit limit per product",
        "version" => "2.0.0",
        "author" => "Joseph Gundo",
        "language" => "english"
    );

    return $configarray;
}

function task_credits_activate()
{
    $pdo = Capsule::connection()->getPdo();
    $pdo->query("CREATE TABLE IF NOT EXISTS `mod_task_credits` (
			`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`pid` INT (11) NOT NULL ,
			`depid` text ,
			`credits` INT ( 11 ) NOT NULL)");


	$pdo->query("CREATE TABLE IF NOT EXISTS `mod_ticket_credits` (                    
                      `id` int(11) NOT NULL AUTO_INCREMENT,                
                      `pid` int(11) NOT NULL,                              
                      `depid` text,                                        
                      `credits` int(11) NOT NULL,                          
                      PRIMARY KEY (`id`)                                   
                    )");

    return array('status' => 'success', 'description' => 'Ticket Credits V2 succesfully activated');

}

function task_credits_deactivate()
{

    //$pdo = Capsule::connection()->getPdo();
    //$pdo->query("DROP TABLE `mod_task_credits`");
    //$pdo->query("DROP TABLE `mod_ticket_credits`");

    return array('status' => 'success', 'description' => 'Ticket Credits V2 succesfully deactivated');
}

function task_credits_output($vars)
{

    $controller = isset($_GET["controller"]) ? $_GET["controller"] : "index";

    if (file_exists(__DIR__ . "/controller/{$controller}.php")) {

        include_once(__DIR__ . "/controller/{$controller}.php");

    } else {

        include_once(__DIR__ . "/controller/index.php");
    }
}

//Task Credit Client Area
function task_credits_clientarea(){

    //Set Command
    $command = $_POST['command'];

    //Update Custom Field From Client Side
    if($command == "update-customfield"){

        //Get Values
        $customfield_id = filter_var($_POST['customfield_id']);
        $new_value = filter_var($_POST['new_value']);
        $ticket_id = filter_var($_POST['ticket_id']);

        //Check Values
        if(!empty($customfield_id) && !empty($new_value) && !empty($ticket_id)){

            $old_value = Capsule::table("tblcustomfieldsvalues")
                ->where("fieldid", $customfield_id)
                ->where("relid", $ticket_id)
                ->first();

            //Update Custom Field Value
            Capsule::table("tblcustomfieldsvalues")
                ->where("fieldid", $customfield_id)
                ->where("relid", $ticket_id)
                ->update([
                    "value" => $new_value
                ]);

            //Log CustomField Update
            localAPI('LogActivity', [
                'description' => "CustomField Updated [CustomFieldId: {$customfield_id} - TicketID: {$ticket_id}]
                 " . $old_value->value . " => " . $new_value
            ]);

            die(json_encode(["result" => "success"]));

        }else{

            die(json_encode(["result" => "error"]));
        }
    }

    die(json_encode(["result" => "error"]));

}