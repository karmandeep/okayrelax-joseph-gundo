<?php
/**
 * Task Credits
 *
 * @package    WHMCS
 * @author     Joseph Gundo
 *
 */

/**
 *  Start Hook Function
 */


use Illuminate\Database\Capsule\Manager as Capsule;

//Check Credits
add_hook('TicketOpen', 1, function ($vars) {

    $user_relation = Capsule::table("mod_ProductCreditRelation")
        ->where("userid", $vars['userid'])
        ->first();

    $user_relation_with_remaining = Capsule::table("mod_ProductCreditRelation")
        ->where("userid", $vars['userid'])
        ->where("remaining", ">", 0)
        ->first();

    $department_id = explode(',', $user_relation->depid);

    if (isset($user_relation_with_remaining->id) && $vars["deptid"] != $department_id['1']) {

        if (in_array($vars['deptid'], $department_id)) {

            $remaining = $user_relation_with_remaining->remaining - 1;
            $spent = $user_relation_with_remaining->spent + 1;

            //Update ProductCreditRelation table fields
            Capsule::table("mod_ProductCreditRelation")
                ->where("id", $user_relation_with_remaining->id)
                ->update([
                    "spent" => $spent,
                    "remaining" => $remaining
                ]);

        } else {

            //Reply Ticket
            localapi("addTicketReply", [
                "ticketid" => $vars["ticketid"],
                "adminusername" => "Auto-Response",
                "message" => "Sorry, you don't have access to contact this assistant. Please contact your dedicated assistant or order on our plans page."
            ]);

            //Update Ticket to Closed
            localapi("updateTicket", [
                "ticketid" => $vars["ticketid"],
                "status" => "Closed"
            ]);
        }

    } elseif ($vars["deptid"] != $department_id['1'] AND $vars['deptname'] != "Support") {

        if (in_array($vars['deptid'], $department_id)) {

            //Reply Ticket
            localapi("addTicketReply", [
                "ticketid" => $vars["ticketid"],
                "adminusername" => "Auto-Response",
                "message" => "Sorry, you don't have any task credits left, so we cannot complete this task."
            ]);

            //Update Ticket to Closed
            localapi("updateTicket", [
                "ticketid" => $vars["ticketid"],
                "status" => "Closed"
            ]);
        } else {

            //Reply Ticket
            localapi("addTicketReply", [
                "ticketid" => $vars["ticketid"],
                "adminusername" => "Auto-Response",
                "message" => "Sorry, you don't have access to contact this assistant. Please contact your dedicated assistant or order on our plans page."
            ]);

            //Update Ticket to Closed
            localapi("updateTicket", [
                "ticketid" => $vars["ticketid"],
                "status" => "Closed"
            ]);
        }
    }
});

//Allow Ticket Generation
add_hook('ClientAreaPage', 1, function ($vars) {

    if ($_POST['AllowTicketGeneration']) {

        $department_id = $_POST['depid'];
        $user_relation = Capsule::table("mod_ProductCreditRelation")
            ->where("userid", $_POST['userid'])
            ->first();

        $user_relation_with_remaining = Capsule::table("mod_ProductCreditRelation")
            ->where("userid", $_POST['userid'])
            ->where("remaining", ">", 0)
            ->whereRaw("LOCATE('," . $department_id . ",',CONCAT(',',depid,','))")
            ->exists();

        $exploded_dept_arr = explode(',', $user_relation->depid);

        $department_info = Capsule::table("tblticketdepartments")
            ->where("id", $exploded_dept_arr[1])
            ->first();

        $service_info = Capsule::table("tblhosting")
            ->where("id", $user_relation->serviceid)
            ->first();

        if ($user_relation_with_remaining && in_array($service_info->domainstatus, ['Active', 'Pending']) || $_POST['depname'] == $department_info->name) {
            die("1");
        } else {
            die("0");
        }
    }

    if ($vars['filename'] == "submitticket") {

        $userid = $vars['clientsdetails']['userid'];

        $selected_dept = Capsule::table("mod_ProductCreditRelation")
            ->where("userid", $userid)
            ->first();

        $depts = explode(",", $selected_dept->depid);
        $departments = array();
        foreach ($depts as $dept) {

            $get_department = Capsule::table("tblticketdepartments")
                ->where("id", $dept)
                ->first();

            if (isset($get_department->id)) {

                $departments[] = (array)$get_department;
            }
        }

        $return['departments'] = $departments;
        return $return;

    } elseif ($vars['filename'] == "cart") {

        $userid = $vars['clientsdetails']['userid'];

        $user_relation = Capsule::table("mod_ProductCreditRelation")
            ->where("userid", $userid)
            ->first();

        $service_info = Capsule::table("tblhosting")
            ->where("id", $user_relation->serviceid)
            ->first();

        if (isset($service_info->domainstatus) && in_array($service_info->domain_status, ['Active', 'Pending'])) {

            $productgroups = $vars['productgroups'];
            $array = array();
            foreach ($productgroups as $key => $value) {
                if ($value['gid'] != "12") {
                    $array[] = array("gid" => $value['gid'], "name" => $value['name']);
                }
            }

            if ($_GET['gid'] == "12") {
                header("location:cart.php");
            }
            $return['productgroups'] = $array;
            return $return;

        }
    }
});

//Restrict Ticket Generation
add_hook('ClientAreaHeaderOutput', 1, function ($vars) {

    if ($vars['filename'] == "submitticket") {

        return "<script>
                jQuery(document).ready(function(){
                    var count = 1;
                    var depid = jQuery('select[name=\"deptid\"]').val();
                    var depname = jQuery('select[name=\"deptid\"] option:selected').text();
                    var userid = '" . $_SESSION['uid'] . "';
                    jQuery('form[name=\"submitticket\"]').submit(function(){
                        if(count == 2){
                            return true;
                        }
                        jQuery.ajax({
                            url : '',
                            data : {'depid': depid, 'depname':depname, 'userid' : userid, 'AllowTicketGeneration' : 'AllowTicketGeneration'},
                            type : 'post',
                            success : function(response){
                                if(response == '1'){
                                    count = 2;
                                    jQuery('form[name=\"submitticket\"]').submit();
                                }else{
                                    jQuery('form[name=\"submitticket\"]').before('<div class=\"alert alert-error\"> <p class=\"bold\">The following errors occurred:</p><ul><li>You need more task credits.</li></ul></div>');
                                }
                            }
                        })
                        return false;
                    });
                })
              </script>";
    }
});

//Validate Checkout to avoid dublicate issue
add_hook('ShoppingCartValidateCheckout', 1, function ($vars) {

    //Get user's active services
    $services = Capsule::table("tblhosting")
        ->where("userid", $vars['userid'])
        ->where("domainstatus", "Active")
        ->get();

    foreach ($services as $service) {

        //Check Existed Services
        $credit_relation = Capsule::table("mod_ProductCreditRelation")
            ->where("serviceid", $service->id)
            ->count();

        if ($credit_relation > 0) {

            return [
                "You already have an active service so can't subscribe a new one."
            ];
        }
    }

    if (count($_SESSION['cart']['products']) > 1) {

        return [
            "You can only have one service. Please remove the other service from your cart."
        ];
    }

});

add_hook('AdminClientServicesTabFields', 1, function ($vars) {

    //Get Relation Line
    $relation = Capsule::table("mod_ProductCreditRelation")
        ->where("serviceid", $vars['id'])
        ->first();

    $explode_dep = explode(",", $relation->depid);

    $department_name = "";

    if($explode_dep[0] > 0){

        $getDepartment = Capsule::table("tblticketdepartments")
            ->where("id", $explode_dep[0])
            ->first();

        $department_name = $getDepartment->name;
    }

    return [
        "Assistant" => '<input type="text" value="' . $department_name .'" class="form-control" name="task_asistant">',
        "Task Credits" => '<input type="text" value="' .
            $relation->remaining .'" class="form-control" name="task_credit"> [ Total: ' . $relation->credits . ' - Spent: ' . $relation->spent . ' ]',

    ];
});
/*
//Add to log when edits related custom fields by admins
add_hook('AdminServiceEdit', 1, function ($vars) {

    if ($_POST['packageid'] > 0) {

        $log_description = "2AdminServiceEdit : ";

        //Get Custom Field IDs
        $custom_field_values = Capsule::table("tblcustomfields")
            ->where("type", "product")
            ->where("relid", $_POST['packageid'])
            ->get();

        foreach ($custom_field_values as $field_value) {

            $log_description .= $field_value->fieldname . " : " . $_POST['customfield'][$field_value->id] . ", ";
        }

        localAPI('logactivity', [
            'userid' => $vars['userid'],
            'description' => rtrim($log_description, ", ")
        ]);
    }
});
*/
//Delete Service Line From Database
add_hook('ServiceDelete', 1, function ($vars) {

    Capsule::table("mod_ProductCreditRelation")
        ->where("serviceid", $vars['serviceid'])
        ->delete();

});

//Set Default DueDate For Ticket
add_hook('CustomFieldSave', 1, function ($vars) {

    if (isset($_POST['customfield'])) {

        //Get Settings
        $settings = Capsule::table('tblconfiguration')
            ->where('setting', 'Task_Credit_Default_DueDate')
            ->first();

		/*Fix By Karmandeep Singh <karmandeep.singh@gmail.com*/
		$settings->value = stripslashes($settings->value);
		$settings->value = str_replace( '\/', '/', $settings->value );
		//$settings->value = json_encode($settings->value);


        $settings = json_decode($settings->value, true);
        //$settings = unserialize($settings->value);
		/*END FIX*/


        //$settings = json_decode($settings->value, true);

        foreach ($_POST['customfield'] as $customfield_key => $customfield_value) {

            //Check Created Ticket Custom Fields
            if (in_array($customfield_key, $settings['customfields']) && empty(trim($customfield_value))) {

                return array('value' => date("Y-m-d", strtotime("+" . $settings['priority'][strtolower($_POST['urgency'])] . " days")));
            }
        }
    }
});

//Add Edit Button For CustomField When View Ticket
add_hook('ClientAreaFooterOutput', 1, function ($vars) {

    //Run Only View Ticket Page
    if ($vars['filename'] == "viewticket") {
        //Get Settings
        $settings = Capsule::table('tblconfiguration')
            ->where('setting', 'Task_Credit_Default_DueDate')
            ->first();

        $settings = json_decode($settings->value, true);

        foreach ($vars['customfields'] as $v) {

            if (in_array($v['id'], $settings['customfields'])) {

                $element_id = "Secondary_Sidebar-Custom_Fields-" . str_replace(" ", "_", $v['name']);

                //Add Edit CustomField Feature
                return <<<HTML
<script type="text/javascript">

$(document).ready(function(){
    
    $("#{$element_id}").html('<strong>{$v['name']}</strong><br>' + 
    '<input id="{$element_id}_textbox" type="date" value="{$v['value']}" style="display:inline;width:66%" class="form-control">' +
    '<button class="btn btn-warning btn-sm" id="{$element_id}_save" data-id="{$vars['id']}" style="margin-top: -5px;margin-left:10px"> Update</button>' +
    '<div id="{$element_id}_result"></div>');

    $("#{$element_id}_save").click(function(){
            
        var ticket_id = $(this).attr("data-id"),
            new_value = $("#{$element_id}_textbox").val(),
            customfield_id = {$v['id']};
        

        $.ajax({
            url: "index.php?m=task_credits",
            type: "POST",
            dataType: "json",
            data: {
                "ticket_id": ticket_id, 
                "new_value": new_value, 
                "customfield_id": customfield_id, 
                "command":"update-customfield"
                },
            success: function(data){
                
                if(data.result == "success"){
                    
                    $("#{$element_id}_result").html("<br><br><span class='label status status-active'>{$v['name']} Value Updated</span>");
                }else {
                    
                    $("#{$element_id}_result").html("<br><br><span class='label status status-cancelled'>An Error Occurred</span>");
                }
            }
        });
    });
});
 
</script>
HTML;
            }
        }
    } elseif ($vars['filename'] == "submitticket") {

        //Get Settings
        $settings = Capsule::table('tblconfiguration')
            ->where('setting', 'Task_Credit_Default_DueDate')
            ->first();

        $settings = json_decode($settings->value, true);

        foreach ($vars['customfields'] as $v) {

            if (in_array($v['id'], $settings['customfields'])) {

                //Add Edit CustomField Feature
                return <<<HTML
<script type="text/javascript">
//Commented as it was causing Issues with Date Selection Field.
/*
$(document).ready(function(){
    
    $("input[name='customfield[{$v['id']}]'").prop('type', 'date');
});
 */
</script>
HTML;
            }
        }
    }
});

//Modify DueDate Button On AdminSide
add_hook('AdminAreaFooterOutput', 1, function ($vars) {


    if ($vars['filename'] == "supporttickets" && isset($_GET['action'])
        && ($_GET['action'] == "viewticket" || $_GET['action'] == "view")) {

        //Get Settings
        $settings = Capsule::table('tblconfiguration')
            ->where('setting', 'Task_Credit_Default_DueDate')
            ->first();

        $settings = json_decode($settings->value, true);

        foreach ($vars['customfields'] as $v) {

            if (in_array($v['id'], $settings['customfields'])) {

                //Convert CustomField Field to Date Field
                return <<<HTML
<script type="text/javascript">

$(document).ready(function(){
    
    $(document).on("click","#customfield{$v['id']}",function() {
    
        $( this ).prop('type', 'date');
    });
    
    $(".nav-tabs").append('<span class="ticketlastreply">Task DueDate: {$v['value']}</span>');
    
});
 
</script>
HTML;
            }
        }
    }
});

//Adding Task Credit Information to Client Area
add_hook('ClientAreaPrimarySidebar', 1, function ($primarySidebar) {

    //Get Remaining Value
    $relation = Capsule::table("mod_ProductCreditRelation as rel")
        ->leftJoin("tblhosting as host", "rel.serviceid", "=", "host.id")
        ->where("host.domainstatus", "Active")
        ->where("rel.userid", $_SESSION['uid'])
        ->select("rel.*")
        ->first();

    if (isset($relation->serviceid)) {

        /*
        $assistant = Capsule::table("tblhosting as host")
            ->join("tblcustomfieldsvalues as val", "val.relid", "=", "host.id")
            ->join("tblcustomfields as cust", "cust.id", "=", "val.fieldid")
            ->where("host.id", $user_relation->serviceid)
            ->where("cust.fieldname", "Assistant")
            ->select("val.value")
            ->first();

        $task_credit = Capsule::table("tblhosting as host")
            ->join("tblcustomfieldsvalues as val", "val.relid", "=", "host.id")
            ->join("tblcustomfields as cust", "cust.id", "=", "val.fieldid")
            ->where("host.id", $user_relation->serviceid)
            ->where("cust.fieldname", "Task Credits")
            ->select("val.value")
            ->first();

        */

        $explode_dep = explode(",", $relation->depid);

        $department_name = "";

        if($explode_dep[0] > 0){

            $getDepartment = Capsule::table("tblticketdepartments")
                ->where("id", $explode_dep[0])
                ->first();

            $department_name = $getDepartment->name;
        }

        $assistant = $department_name;

		/*Capsule::table("tblhosting as host")
            ->join("tblcustomfieldsvalues as val", "val.relid", "=", "host.id")
            ->join("tblcustomfields as cust", "cust.id", "=", "val.fieldid")
            ->where("host.id", $user_relation->serviceid)
            ->where("cust.fieldname", "Assistant")
            ->select("val.value")
            ->first();*/
		
        if(isset($_GET['tid']) && $_GET['tid'] != NULL):          
        
            $ticket_id = $_GET['tid'];
            
            $duedate_qry = Capsule::table('tblcustomfieldsvalues')
                      ->join('tblcustomfields', 'tblcustomfieldsvalues.fieldid', '=', 'tblcustomfields.id')
                      ->join('tbltickets', 'tblcustomfieldsvalues.relid', '=', 'tbltickets.id')
                      ->where('tblcustomfields.type', 'support')
                      ->where('tbltickets.tid', $ticket_id)
                      ->where('tblcustomfields.fieldname', 'Due Date')
                      ->select('tblcustomfieldsvalues.relid as ticketid' , 'tblcustomfields.fieldname' , 'tblcustomfieldsvalues.value')
                      ->first();
    
            
            if(count($duedate_qry) > 0) {
                if (!is_null($primarySidebar->getChild('Ticket Information'))) {
                
                    $primarySidebar->getChild('Ticket Information')
                    ->addChild('due-date')
                    ->setClass('ticket-details-children')
                    ->setLabel('<span class="title">' . $duedate_qry->fieldname . '</span><br>' . $duedate_qry->value) 
                    ->setOrder(100);
                }
            }
        endif;
        
        //Add Task Menu
        $newMenu = $primarySidebar->addChild(
            'taskCredits',
            array(
                'name' => 'taskCredits',
                'label' => "Task Credits",
                'order' => 99,
                'icon' => "fa-info-circle fa-fw"
            )
        );

        if(!empty($assistant)) {
            //Add Assistant Menu
            $newMenu->addChild(
                'taskCreditsAssistant',
                array(
                    'name' => 'TaskCredits Assistant',
                    'label' => "<strong>Assistant:</strong> " . $assistant,
                    'order' => 10,
                )
            );
        }

        //Add Remaining Menu
        $newMenu->addChild(
            'taskCreditsRemaining',
            array(
                'name' => 'TaskCredits Remaining',
                'label' => "<strong>Remaining:</strong> " . $relation->remaining,
                'order' => 20,
            )
        );
    }
});
