<link href="../modules/addons/{$smarty.get.module}/css/main.css" rel="stylesheet" type="text/css" />
<script src="../modules/addons/{$smarty.get.module}/js/main.js" type="text/javascript"></script>
<ul class="nav nav-tabs admin-tabs">
    <li class="{if !isset($smarty.get.controller)} active {/if}">
        <a href="addonmodules.php?module=task_credits">List Credits</a>
    </li>
    <li class="{if $smarty.get.controller == "addmore"} active {/if}">
        <a href="addonmodules.php?module=task_credits&controller=addmore">Add New Product to System</a>
    </li>
    <li>
        <a href="addonmodules.php?module=task_credits&controller=settings"> Department Settings</a>
    </li>
    <li>
        <a href="addonmodules.php?module=task_credits&controller=duedatesetting"> DueDates Settings</a>
    </li>
</ul>
<div class="tab-content">
    <div class="tab-pane active">
        {if isset($delete_result) && $delete_result == true}
            <div class="dangerbox">
                <strong>
                    <span class="title">Saved Successfully!</span>
                </strong>
                <br>Product Added to System Successfully.
            </div>
        {/if}
        {if isset($smarty.get.restoremessage) && $smarty.get.restoremessage == "success"}
            <div class="successbox">
                <strong>
                    <span class="title">Saved Successfully!</span>
                </strong>
                <br>Restore Process Finished
            </div>
        {/if}
        <div class="tablebg">
            <table class="datatable" width="100%">
                <thead>
                <th>Product Name</th>
                <th>Assistant</th>
                <th>Credits</th>
                <th></th>
                </thead>
                <tbody>
                {foreach from=$ticket_credits item=v}
                    <tr>
                        <td> <a href="configproducts.php?action=edit&id={$v.pid}" target="_blank">{$v.productname}</a> </td>
                        <td> {$v.department} </td>
                        <td> {$v.credits} </td>
                        <td>
                            <a class="btn btn-warning btn-sm" href="addonmodules.php?module=task_credits&controller=editline&lineid={$v.id}">Edit</a>
                            <a class="btn btn-danger btn-sm"  href="addonmodules.php?module=task_credits&controller=delete&lineid={$v.id}"
                               onclick="return confirm('Are You Sure Delete This Line ?')">Delete</a>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>

    </div>
</div>