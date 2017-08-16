<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-param" content="_csrf">
    <meta name="csrf-token" content="{$csrftoken}">
    <title></title>
    <link href="/css/bootstrap.css" rel="stylesheet">
<link href="/css/site.css" rel="stylesheet">
</head>
<body>

<div class="wrap">
    {include '../admin/header.tpl'}
    <div class="container">

<div class="site-login">
    <h1>农业机激活码列表</h1>

    <p>农业机激活码列表             <a href='/adminsn/add/'>增加农业机激活码</a>&nbsp;&nbsp;  <a href="/adminsn/listbody">sn和代理</a></p>

    <p>
        <form action="/adminsn/list" method="post" >
        <input type="hidden" name="_csrf" value="{$csrftoken}">
        <div>&nbsp;整机序列号&nbsp;&nbsp;<input type="text" value="{$body_code|escape:"html"}" id="body_code" name="body_code" style="display:inline;">&nbsp;&nbsp;&nbsp;硬件id&nbsp;&nbsp;<input type="text" value="{$hardware_id|escape:"html"}" id="hardware_id" name="hardware_id" style="display:inline;">&nbsp;&nbsp;<input type="submit" style="display:inline;" value="查询" > &nbsp;</div> 
       </form>
    </p>


      <table cellspacing="10" cellpadding="10" border="1" width="100%">
    
        <thead>
            <tr>
                <th >id</th>
                <th >整机序列号</th>
                <th>硬件id</th>
                <th >激活码</th>
                <th>日期</th>
                <th >型号</th>
                <th>操作人员</th>
                <th>代理名称</th>
                <th>代理code</th>               
                <th>操作</th>
            </tr>
        </thead>
        
        <tbody id="tbody">
           {foreach from=$LIST key="mykey" item=user}
           <tr>
            <td>{$user.id|escape:"html"}</td>
            <td>{$user.body_code|escape:"html"}</td>
            <td>{$user.hardware_id|escape:"html"}</td>
            <td>{$user.activation|escape:"html"}</td>
            <td>{$user.created_at|escape:"html"}</td>
            <td>{$user.type|escape:"html"}</td>
            <td>{$user.operator|escape:"html"}</td>  
            {if $user.body }
            <td>{$user.body.0.agentname|escape:"html"}</td>
            <td>{$user.body.0.code|escape:"html"}</td>
            {else}
            <td></td>
            <td></td>
            {/if}         
            <td><a href='/adminsn/add/?id={$user.id|escape:"html"}'>修改</a></td>               
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