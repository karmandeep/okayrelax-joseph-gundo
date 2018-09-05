<script src="../modules/addons/{$smarty.get.module}/js/main.js" type="text/javascript"></script>
<link href="../modules/addons/{$smarty.get.module}/css/main.css" rel="stylesheet" type="text/css" />

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

{if isset($db_result) && $db_result == true}
<div class="successbox">
    <strong>
        <span class="title">Saved Successfully!</span>
    </strong>
    <br>Product Added to System Successfully.
</div>
{/if}

<div class="tab-content">
    <div class="tab-pane active">
    <form action="addonmodules.php?module=task_credits&controller=addmore" method="post">
        <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
            <tbody>
            <tr>
                <td class="fieldlabel">Product : </td>
                <td class="fieldarea">
                    <select name="pid" class="form-control select-inline">
                        {foreach from=$products item=product}
                            {if $product.module == "TaskCredits_v2"}
                                <option value="{$product.pid}" {if in_array($product.pid, $ticket_credits)}disabled{/if}>
                                    {$product.name}
                                </option>
                            {/if}
                        {/foreach}
                    </select>
                </td>
            </tr>
            <tr>
                <td class="fieldlabel">Department : </td>
                <td class="fieldarea">
                    <select name="depid" class="form-control select-inline">
                        {foreach from=$departments item=department}
                            <option value="{$department.id}">{$department.name}</option>
                        {/foreach}
                    </select>
                </td>
            </tr>
            <tr>
                <td class="fieldlabel">Credit : </td>
                <td class="fieldarea">
                    <input type="number" name="credits" class="form-control input-250" value="">
                </td>
            </tr>
            </tbody>
        </table>
        <div class="btn-container">
            <input type="submit" value="Add New Product to System" class="button btn btn-primary">
            <a class="btn btn-default" href="addonmodules.php?module=task_credits">Cancel</a>
        </div>
    </form>
    </div>
</div>