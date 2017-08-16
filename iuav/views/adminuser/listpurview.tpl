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
    <h1>访问地址列表</h1>

    <p>访问地址列表             <a href='/adminuser/addpurview/'>增加访问地址</a></p>
      <table cellspacing="10" cellpadding="10" border="1" width="100%">
    
        <thead>
            <tr>
                <th >id</th>
                <th >上一级id</th>
                <th >链接名称</th> 
                <th >链接地址</th>
                <th >类名称和方法名</th>
                <th >描述</th>
                <th>操作</th>
            </tr>
        </thead>
        
        <tbody id="tbody">
           {foreach from=$LIST key="mykey" item=user}
           <tr>
            <td>{$user.id}</td>
            <td>{$user.upper_purview_id}</td>
            <td>{$user.redirect_name}</td>
            <td>{$user.redirect_url}</td>
            <td>{$user.method}</td>
            <td>{$user.description}</td>           
            <td><a href='/adminuser/addpurview/?id={$user.id}'>修改</a> &nbsp;&nbsp;  </td>               
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