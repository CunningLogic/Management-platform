<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-param" content="_csrf">
    <meta name="csrf-token" content="{$csrftoken}">
    <title>{$headtitle}</title>
    <link href="/css/bootstrap.css" rel="stylesheet">
<link href="/css/site.css" rel="stylesheet">
</head>
<body>

<div class="wrap">
    {include '../admin/header.tpl'}    
    <div class="container">

<div class="site-login">
   
    <h1>售后查询</h1>

    <p>售后查询&nbsp;&nbsp;</p>
    <p>
        <form action="/adminuser/findapply/" method="post" >
        <input type="hidden" name="_csrf" value="{$csrftoken}">
        <div style="font-size:12px;" class="row">
            &nbsp;机身序列&nbsp;<input type="text" value="{$body_code|escape:"html"}" id="body_code" name="body_code" style="display:inline;" />           
            &nbsp;硬件id&nbsp;<input type="text" value="{$hardware_id|escape:"html"}" id="hardware_id" name="hardware_id" style="display:inline;">
           &nbsp;<input type="submit" style="display:inline;" value="查询" > &nbsp;</div>
       </form>
    </p>

      <table cellspacing="10" cellpadding="10" border="1" width="100%">
    
        <thead>
            <tr>
                <th style="cursor:point;" >id</th>
                <th>请求id</th>
                <th style="cursor:point;" >机身序列</th>               
                <th>公司/用户</th>
                <th>手机号</th>
                <th>代理名称</th>
                <th>上级代理名称</th>
                <th>保险状态</th>
                <th>DJI账号</th>
                <th>省份</th>
                <th>城市</th>
                <th>地址</th>
                <th>创建时间</th>
                
            </tr>
        </thead>        
        <tbody id="tbody">
           {foreach from=$LIST key="mykey" item=user}
           <tr>
            <td>{$user.id|escape:"html"}</td>
            <td>{$user.apply_id|escape:"html"}</td>
            <td>{$user.body_code|escape:"html"}</td>           
            <td>{$user.company_name|escape:"html"}/{$user.realname|escape:"html"}</td>
            <td>{$user.phone|escape:"html"}</td>
            <td>{$user.agentname|escape:"html"}</td>
            <td>{$user.upperagentname|escape:"html"}</td>
            <td>{$user.polnostr|escape:"html"}</td>
            <td>{$user.account|escape:"html"}</td>
            <td>{$user.province|escape:"html"}</td>
            <td>{$user.city|escape:"html"}</td>
            <td>{$user.area|escape:"html"}{$user.street|escape:"html"}{$user.address|escape:"html"}</td>          
            <td>{$user.created_at|escape:"html"}</td>
          
                      
          </tr>
           {/foreach}           
        </tbody>
    
    </table>
 
  {if $page_count > 1}
  <p> &nbsp;</p>

   <table>
            <tr>
                <td>
                    共{$count}条数据 每页{$size}条 共{$page_count}页 当前第{$page}页
                </td>
                <td>
                    {if $page neq 1}
                        <a href="{$base_url}" class="pageNum">首页</a>
                    {else}>
                        <b>首页</b>
                    {/if}
                    {if ($page-1) > 1}
                        <a href="{$base_url}&page={$page-1}" class="pageNum">上一页</a>
                    {else}<b>上一页</b>{/if}
                    {section name=loop loop="$page_count"}                       
                        {if $smarty.section.loop.index+1 eq $page}
                            <b>{$page}</b> 
                        {else}
                           <a href="{$base_url}&page={$smarty.section.loop.index+1}" class="pageNum">{$smarty.section.loop.index+1}</a>
                        {/if}
                    {/section}
                    {if ($page+1) <= $page_count }
                        <a href="{$base_url}&page={$page+1}" class="pageNum">下一页</a>
                    {else}<b>下一页</b>{/if}

                    {if $page eq $page_count}
                        <b>尾页</b>
                    {else}
                        <a href="{$base_url}&page={$page_count}" class="pageNum">尾页</a>
                    {/if}
                </td>
            </tr>
        </table>
   {/if}


</div>


    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; DJI 2016</p>       
    </div>
</footer>
<script src="/js/jquery.js"></script>
<script src="/js/bootstrap.js"></script>


</body>
</html>