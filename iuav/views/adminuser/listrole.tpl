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
    <h1>角色列表</h1>

    <p>角色列表             <a href='/adminuser/addrole/'>增加角色</a></p>
      <table cellspacing="10" cellpadding="10" border="1" width="100%">
    
        <thead>
            <tr>
                <th style="cursor:point;" onclick="order_by('id')">id</th>
                <th style="cursor:point;" onclick="order_by('type')">名称</th>
                <th style="cursor:point;" onclick="order_by('name')">排序</th> 
                <th>操作</th>
            </tr>
        </thead>
        
        <tbody id="tbody">
           {foreach from=$LIST key="mykey" item=user}
           <tr>
            <td>{$user.id}</td>
            <td>{$user.name}</td>
            <td>{$user.sort_order}</td>
            <td><a href='/adminuser/addrole/?id={$user.id}'>修改</a> &nbsp; <a href='/adminuser/addrolepurview/?roleid={$user.id}'>增加权限</a> &nbsp;<a href='/adminuser/adduserrole/?roleid={$user.id}'>增加用户</a> &nbsp; <a href='/adminuser/addrole/?id={$user.id}'>查看权限</a> </td>               
          </tr>
           {/foreach}
           
        </tbody>
    
    </table>
</div>


    </div>
</div>


{include '../admin/footer.tpl'}
</body>
</html>