<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>代理商管理平台</title>

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
        {include "left_side.tpl" nav="notice"}

        <div class="main-side">
            {include "top.tpl"}

            <div class="main-body">

                <div class="list-section manamgement-list">
                    
                    <div class="list-section-body">
                        <table class="table table-striped table-without-border apply-table">
                            <thead>
                                <tr>                                   
                                    <th>序号</th>
                                    <th>标题</th>
                                    <th>内容</th>
                                    <th>日期</th>                                  
                                </tr>
                            </thead>
                            <tbody>
                                {if isset($data)}
                                    {foreach from=$data key="mykey" item=activedata}
                                        <tr>
                                            <td>{$activedata.id|escape:"html"}</td>
                                            <td>{$activedata.title|escape:"html"}</td>
                                            <td>{$activedata.content|escape:"html"}</td>
                                            <td>{$activedata.updated_at|escape:"html"}</td>                                        
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

    
    <script type="text/javascript" data-main="/public/js/management.js?v=7" src="/public/js/externals/require-2.1.11.min.js"></script>
{include "footer.tpl"}
</body>
</html>