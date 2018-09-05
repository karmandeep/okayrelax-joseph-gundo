<script src="../modules/addons/{$smarty.get.module}/js/main.js" type="text/javascript"></script>
<link href="../modules/addons/{$smarty.get.module}/css/main.css" rel="stylesheet" type="text/css" />

<ul class="nav nav-tabs admin-tabs">
    <li>
        <a href="addonmodules.php?module=task_credits">List Credits</a>
    </li>
    <li>
        <a href="addonmodules.php?module=task_credits&controller=addmore">Add New Product to System</a>
    </li>
    <li class="active">
        <a href="addonmodules.php?module=task_credits&controller=settings"> Department Settings</a>
    </li>
    <li>
        <a href="addonmodules.php?module=task_credits&controller=duedatesetting"> DueDates Settings</a>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane active">
        {if isset($db_result) && $db_result > 0}
            <div class="successbox">
                <strong>
                    <span class="title">Saved Successfully!</span>
                </strong>
                <br>Settings Updated Successfully. ( {$db_result} Customfield Value Updated)
            </div>
        {elseif isset($db_result) && $db_result == 0}
            <div class="infobox">
                <strong>
                    <span class="title">Saved Successfully!</span>
                </strong>
                <br>No CustomField Updated.
            </div>
        {/if}
    <form action="addonmodules.php?module=task_credits&controller=settings" method="post">
        <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
            <tbody>
            <tr>
                <td class="fieldlabel">Current Department : </td>
                <td class="fieldarea">
                    <select name="current_depid" class="form-control select-inline">
                        {foreach from=$departments item=department}
                            <option value="{$department.id}">{$department.name}</option>
                        {/foreach}
                    </select>
                </td>
            </tr>
            <tr>
                <td class="fieldlabel">New Department : </td>
                <td class="fieldarea">
                    <select name="new_depid" class="form-control select-inline">
                        {foreach from=$departments item=department}
                            <option value="{$department.id}">{$department.name}</option>
                        {/foreach}
                    </select>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="btn-container">
            <input type="submit" value="Save Changes" class="button btn btn-primary">
            <a class="btn btn-default" href="addonmodules.php?module=task_credits">Cancel</a>
        </div>
    </form>
    </div>
</div>