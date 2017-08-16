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
    <h1>用户列表</h1>

    <p>用户列表             <a href='/adminuser/add/'>增加新用户</a></p>
      <table cellspacing="10" cellpadding="10" border="1" width="100%">
    
        <thead>
            <tr>
                <th style="cursor:point;" onclick="order_by('id')">id</th>
                <th style="cursor:point;" onclick="order_by('type')">用户名</th>
                <th style="cursor:point;" onclick="order_by('name')">google auth </th>
                <th style="cursor:point;" onclick="order_by('name')">remark</th>
                <th >角色</th>
                <th>操作</th>
            </tr>
        </thead>
        
        <tbody id="tbody">
           {foreach from=$LIST key="mykey" item=user}
           <tr>
            <td>{$user.id}</td>
            <td>{$user.username}</td>
            <td><a href='https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=otpauth%3A%2F%2Ftotp%2Fiuav{$user.id}%3Fsecret%3D{$user.google_auth}'>{$user.google_auth}</a></td>
            <td>{$user.remark}</td>
            <td>{$user.role_id}</td>
            <td><a href='/adminuser/add/?id={$user.id}'>修改</a> &nbsp;&nbsp; <a href='/adminuser/adduserrole/?id={$user.id}'>增加权限</a> </td>               
          </tr>
           {/foreach}
           
        </tbody>
    
    </table>
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