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
    
    <h1>mis代理用户列表</h1>

    <p>mis理用户列表&nbsp;&nbsp;<a href='/adminuser/listagent/'>代理用户</a>&nbsp;<a href='/adminuser/listagentmis/?email=null'>邮箱为空</a></p>

    <p>
        <form action="/adminuser/listagentmis/" method="post" >
        <input type="hidden" name="_csrf" value="{$csrftoken}">
        <div style="font-size:12px;">&nbsp;代理code&nbsp;&nbsp;<input type="text" value="{$code|escape:"html"}" id="code" name="code" style="display:inline;">&nbsp;代理uid&nbsp;&nbsp;<input type="text" value="{$misuid|escape:"html"}" id="misuid" name="misuid" style="display:inline;">&nbsp;
        邮箱&nbsp;&nbsp;<input type="text" value="{$email|escape:"html"}" id="email" name="email" style="display:inline;">&nbsp;
        &nbsp;<input type="submit" style="display:inline;" value="查询" > &nbsp;</div> 
       </form>
    </p>


      <table cellspacing="10" cellpadding="10" border="1" width="100%">
    
        <thead>
            <tr>
                <th style="cursor:point;" >id</th>
                <th style="cursor:point;" >国家</th>
                <th style="cursor:point;" >省份</th>
                <th style="cursor:point;">城市</th>
                <th style="cursor:point;" >邮箱</th>
                <th style="cursor:point;">代理名称</th>               
                <th style="cursor:point;">负责人</th>
                <th style="cursor:point;">手机号</th>
                <th style="cursor:point;" >地址</th>                
                <th style="cursor:point;" >代理code</th>
                <th style="cursor:point;" >代理uid</th>
                <th>操作</th>
            </tr>
        </thead>        
        <tbody id="tbody">
           {foreach from=$LIST key="mykey" item=user}
           <tr>
            <td>{$user.id}</td>
            <td>{$user.country}</td>
            <td>{$user.province}</td>
            <td>{$user.city}</td>
            <td>{$user.email}</td>
            <td>{$user.agentname}</td>
            <td>{$user.realname}</td>
            <td>{$user.phone}</td>
            <td>{$user.address}</td>           
            <td>{$user.code}</td>
            <td>{$user.misuid}</td>
            <td><a href='/adminuser/addagent/?id={$user.id}&from=mis'>增加代理</a> &nbsp;&nbsp;</td>               
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