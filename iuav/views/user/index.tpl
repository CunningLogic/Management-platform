<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>DJI农业植保机用户中心</title>

    <link rel="stylesheet" type="text/css" href="/public/css/externals/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="/public/css/common.css"/>
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
<body>

    <div class="page-container">
        {include "left_side.tpl" nav="management"}

        <div class="main-side">
            {include "top.tpl"}

            <div class="main-body">

                <div class="list-section manamgement-list">
                    <div class="list-section-head">
                       
                    </div>
                    <div class="list-section-body">
                        <table class="table table-striped table-without-border apply-table">
                            <thead>
                                <tr>
                                    <th>获取激活码时间</th>
                                    <th>硬件ID</th>
                                    <th>整机序列号</th>
                                    <th>保险单</th>
                                    <th>代理商</th>
                                </tr>
                            </thead>
                            <tbody>
                                {if isset($data)}
                                    {foreach from=$data key="mykey" item=activedata}
                                        <tr>                                           
                                            <td>{$activedata.created_at}</td>
                                            <td>{$activedata.hardware_id}</td>
                                            <td>{$activedata.body_code|escape:"html"}</td>
                                            <td>
                                                {if $activedata.pol_no eq $activedata.polnostr}
                                                    <a href="javascript:;" action-type="modal" modal-data="{$activedata.pol_no|escape:"html"}">保险单</a>
                                                {else}
                                                    <span class="status-danger">{$activedata.polnostr|escape:"html"}</span>
                                                {/if}
                                            </td>
                                            <td>
                                                {$activedata.agentname|escape:"html"}<br/>
                                                {$activedata.agentphone|escape:"html"}
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
                                                <span aria-hidden="true">上一页</span>
                                            </a>
                                        {else}
                                            <span aria-hidden="true">上一页</span>
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
                                                <span aria-hidden="true">下一页</span>
                                            </a>
                                        {else}
                                            <span aria-hidden="true">下一页</span>
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

                        <p class="info-button">
                            <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" data-main="/public/js/management.js" src="/public/js/externals/require-2.1.11.min.js"></script>
    {include "footer.tpl"}
</body>
</html>