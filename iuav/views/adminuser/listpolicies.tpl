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
   
    <h1>保险列表</h1>

    <p><a href='/adminuser/listpolicies/'>保险列表</a>&nbsp;&nbsp;<a href='/adminuser/listpolicies/?pol_no=null'>保险单为空</a>&nbsp;&nbsp;<a href='/adminuser/nopolicies/'>异常列表</a>&nbsp;&nbsp;<a href='/adminexcel/checkpolicy'>保险订单核对</a>&nbsp;<a href='/adminexcel/markpolicy'>保险订单财务标记</a></p>
    <p>
        <form action="/adminuser/listpolicies/" method="post" >
        <input type="hidden" name="_csrf" value="{$csrftoken}">
        <div>&nbsp;请求id&nbsp;&nbsp;<input type="text" value="{$apply_id|escape:"html"}" id="apply_id" name="apply_id" style="display:inline;">&nbsp;&nbsp;&nbsp;query_id&nbsp;&nbsp;<input type="text" value="{$query_id|escape:"html"}" id="query_id" name="query_id" style="display:inline;">&nbsp;&nbsp;保险单&nbsp;&nbsp;<input type="text" value="{$pol_no|escape:"html"}" id="pol_no" name="pol_no" style="display:inline;">&nbsp;&nbsp;order_id&nbsp;&nbsp;<input type="text" value="{$order_id|escape:"html"}" id="order_id" name="order_id" style="display:inline;">&nbsp;&nbsp;
        <br />
         <br />
            开始日期&nbsp;<input type="date" value="{$begin|escape:"html"}" id="begin" name="begin" style="display:inline;">&nbsp;&nbsp;截止日期&nbsp;<input type="date" value="{$end|escape:"html"}" id="end" name="end" style="display:inline;">&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" style="display:inline;" value="查询" > 
            &nbsp;&nbsp;<a href='/adminexcel/downlistpolicies/?begin={$begin|escape:"html"}&end={$end|escape:"html"}'>下载</a></div> 
       </form>
    </p>

      <table cellspacing="10" cellpadding="10" border="1" width="100%">
    
        <thead>
            <tr>
                <th style="cursor:point;" >id</th>
                <th style="cursor:point;" >请求id</th>
                <th style="cursor:point;" >order_id</th>
                <th style="cursor:point;" >query_id</th>
                <th style="cursor:point;" >保险单</th>
                <th style="cursor:point;" >开始时间</th>
                <th style="cursor:point;" >过期时间</th> 
                <th style="cursor:point;" >amount</th>
                <th style="cursor:point;" >premium</th>              
                <th style="cursor:point;">更新时间</th>               
            </tr>
        </thead>        
        <tbody id="tbody">
           {foreach from=$LIST key="mykey" item=user}
           <tr>
            <td>{$user.id}</td>           
            <td>{$user.apply_id}</td>
            <td>{$user.order_id}</td>
            <td>{$user.query_id}</td>
            <td>{$user.pol_no}</td>
            <td>{$user.eff_tm}</td>
            <td>{$user.exp_tm}</td>
            <td>{$user.amount}</td>
            <td>{$user.premium}</td>
            <td>{$user.updated_at}</td>
          
                        
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

        <p class="pull-right">Powered by <a href="http://www.yiiframework.com/" rel="external">DJI</a></p>
    </div>
</footer>
<script src="/js/jquery.js"></script>
<script src="/js/bootstrap.js"></script>
</body>
</html>