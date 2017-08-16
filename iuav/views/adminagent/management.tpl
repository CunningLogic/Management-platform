<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>{$LANGDATA.iuav_title}</title>

    <link rel="stylesheet" type="text/css" href="/public/css/externals/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="/public/css/common.css?v=17"/>
    <style type="text/css">
    {literal}
        #infoModal {}
        #infoModal .info-box {
            padding: 20px 0;
        }
        #infoModal .info-title {
            color: #707473;
        }
        #infoModal .info-data {
            color: #35c26f;
        }
        #infoModal .info-title,
        #infoModal .info-data {
            margin-bottom: 15px;
            font-size: 16px;
        }
        #infoModal .info-button {
            margin-top: 40px;
        }
        #infoModal .info-button .btn {
            padding-left: 40px;
            padding-right: 40px;
        }
    {/literal}
    </style>
</head>
<body class="{$country}">

    <div class="page-container">
        {include "left_side.tpl" nav="management"}

        <div class="main-side">
            {include "top.tpl"}

            <div class="main-body">

                <div class="list-section manamgement-list">
                    <div class="list-section-head">
                        <div class="search-box">
                            <form class="form-inline" id="searchForm">
                                <div class="form-group">
                                    <select class="form-control" name="typename">
                                        <option value="body_code">{$LANGDATA.iuav_aircraft_number}</option>
                                        <option value="hardware_id">{$LANGDATA.iuav_hardware_id}</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                <input type="text" class="form-control" name="typevalue" placeholder="{$LANGDATA.iuav_search}" value="{$typevalue|escape:"html"}"/>
                                </div>
                                <dif class="form-group">
                                    <div class="input-group date">
                                        <input type="text" class="form-control" id="datePicker" name="date"/>
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                    </div>
                                </dif>
                                <div class="form-group">
                                    <button type="submit" class="button button-default">{$LANGDATA.iuav_search}</button>
                                    <a href="/adminagent/management" class="button button-default">{$LANGDATA.iuav_remove_search}</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="list-section-body">
                        <table class="table table-striped table-without-border apply-table">
                            <thead>
                                <tr>
                                    {if $UPPERAGENTID eq '0' }
                                    <th>{$LANGDATA.iuav_branch_account}</th>
                                    {/if}
                                    <th>{$LANGDATA.iuav_active_date}</th>
                                    <th>{$LANGDATA.iuav_hardware_id}</th>
                                    <th>{$LANGDATA.iuav_aircraft_number}</th>
                                    <th>{$LANGDATA.iuav_compay_name_name}</th>
                                    <th>{$LANGDATA.iuav_phone}</th>
                                    {if $country eq 'cn' }
                                     <th>{$LANGDATA.iuav_insurance_number}</th>
                                    {/if}
                                    <th>{$LANGDATA.iuav_option}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {if isset($data)}
                                    {foreach from=$data key="mykey" item=activedata}
                                        <tr>
                                            {if $UPPERAGENTID eq '0' }
                                            <td>{$activedata.agentname|escape:"html"}</td>
                                            {/if}
                                            <td>{$activedata.created_at}</td>
                                            <td>{$activedata.hardware_id}</td>
                                            <td>{$activedata.body_code|escape:"html"}</td>
                                            <td>
                                                {if $activedata.company_name neq ""}
                                                    {$activedata.company_name|escape:"html"}/{$activedata.realname|escape:"html"}
                                                {else}
                                                    {$activedata.realname|escape:"html"}
                                                {/if}
                                            </td>
                                            <td>
                                                {$activedata.phone}
                                            </td>
                                            {if $country eq 'cn' }
                                            <td>
                                                {if $activedata.pol_no eq $activedata.polnostr}
                                                    <a href="" data-toggle="modal" data-target="#infoModal" data-code="{$activedata.pol_no|escape:"html"}">保险单</a>
                                                {else}
                                                    <span class="status-danger">{$activedata.polnostr|escape:"html"}</span>
                                                {/if}
                                            </td>
                                            {/if}
                                            <td>
                                                <a href="{*WTF*}" data-toggle="modal" data-target="#infoModal" data-code="{$activedata.activation|escape:"html"}">{$LANGDATA.iuav_active_code}</a>
                                                <a href="javascript:;" class="copy icon-copy" data-clipboard-text="{$activedata.activation|escape:"html"}">{$LANGDATA.iuav_copy}</a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {/if}
                            </tbody>
                        </table>
                        <nav>
                            <ul class="pagination">
                                {if isset($page_count) && $page_count > 1}


                                    <li{if ($page - 1) < 1} class="disabled"{/if}>
                                        {if ($page - 1) >= 1}
                                            <a href="{$base_url}&page={$page - 1}" aria-label="Previous" >
                                                <span aria-hidden="true">{$LANGDATA.iuav_page_up}</span>
                                            </a>
                                        {else}
                                            <span aria-hidden="true">{$LANGDATA.iuav_page_up}</span>
                                        {/if}
                                    </li>

                                    {for $p=1 to $page_count}
                                        <li{if $p == $page} class="active"{/if}>
                                            <a href="{$base_url}&page={$p}" >{$p}</a>
                                        </li>
                                    {/for}
                                    
                                    <li{if ($page + 1) > $page_count} class="disabled"{/if}>
                                        {if ($page + 1) <= $page_count}
                                            <a href="{$base_url}&page={$page+1}" aria-label="Next">
                                                <span aria-hidden="true">{$LANGDATA.iuav_page_down}</span>
                                            </a>
                                        {else}
                                            <span aria-hidden="true">{$LANGDATA.iuav_page_down}</span>
                                        {/if}

                                    </li>
                                {/if}
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="text-center info-box">
                        <p class="info-title"></p>
                        <p class="info-data"></p>
                        <a href="javascript:;" class="copy icon-copy" data-clipboard-text="">{$LANGDATA.iuav_copy}</a>

                        <p class="info-button">
                            <button type="button" class="btn btn-default" data-dismiss="modal">{$LANGDATA.iuav_close}</button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" data-main="/public/js/management.js?v=17" src="/public/js/externals/require-2.1.11.min.js"></script>
{include "footer.tpl"}
</body>
</html>