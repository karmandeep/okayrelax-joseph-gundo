<script src="../modules/addons/{$smarty.get.module}/js/main.js" type="text/javascript"></script>
<link href="../modules/addons/{$smarty.get.module}/css/main.css" rel="stylesheet" type="text/css" />

<ul class="nav nav-tabs admin-tabs">
    <li>
        <a href="addonmodules.php?module=task_credits">List Credits</a>
    </li>
    <li>
        <a href="addonmodules.php?module=task_credits&controller=addmore">Add New Product to System</a>
    </li>
    <li>
        <a href="addonmodules.php?module=task_credits&controller=settings"> Department Setting</a>
    </li>
    <li class="active">
        <a href="addonmodules.php?module=task_credits&controller=duedatesetting"> DueDate Setting</a>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane active">
        {if isset($db_result) && $db_result > 0}
            <div class="successbox">
                <strong>
                    <span class="title">Saved Successfully!</span>
                </strong>
                <br>Settings Updated Successfully. ( {$db_result} Default Due Date Values Updated)
            </div>
        {/if}
    <form action="addonmodules.php?module=task_credits&controller=duedatesetting" method="post">
        <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
            <tbody>
            <tr>
                <td class="fieldlabel"> Low </td>
                <td class="fieldarea">
                    <input type="number" name="priority[low]" value="{$settings.priority.low}" class="form-control" placeholder="Days">
                </td>
            </tr>
            <tr>
                <td class="fieldlabel"> Medium </td>
                <td class="fieldarea">
                    <input type="number" name="priority[medium]" value="{$settings.priority.medium}" class="form-control" placeholder="Days">
                </td>
            </tr>
            <tr>
                <td class="fieldlabel"> High </td>
                <td class="fieldarea">
                    <input type="number" name="priority[high]" value="{$settings.priority.high}" class="form-control" placeholder="Days">
                </td>
            </tr>
            {foreach from=$departments item=department}
                <tr>
                    <td class="fieldlabel"> {$department.name} </td>
                    <td class="fieldarea">
                        <select name="customfields[{$department.id}]" class="form-control">
                            <option value="">Please Select a CustomField</option>
                            {foreach from=$department.customfields item=customfield}
                                <option {if $customfield.id == $settings['customfields'][$department.id]}selected{/if}
                                        value="{$customfield.id}">{$customfield.fieldname} [{$customfield.description}]
                                </option>
                            {/foreach}
                        </select>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
        <div class="btn-container">
            <input type="submit" value="Save Changes" class="button btn btn-primary">
            <a class="btn btn-default" href="addonmodules.php?module=task_credits">Cancel</a>
        </div>
    </form>
    </div>
</div>