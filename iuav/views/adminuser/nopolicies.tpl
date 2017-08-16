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
   
    <h1>异常列表</h1>

    <p><a href='/adminuser/listpolicies/'>保险列表</a>&nbsp;&nbsp;<a href='/adminuser/listpolicies/?pol_no=null'>保险单为空</a></p>
   

      <table cellspacing="10" cellpadding="10" border="1" width="100%">
    
        <thead>
            <tr>
                <th style="cursor:point;" >id</th>               
                <th style="cursor:point;" >order_id</th>
                <th style="cursor:point;" >realname</th>
                <th style="cursor:point;" >idcard</th>
                <th style="cursor:point;" >phone</th>                        
                <th style="cursor:point;">更新时间</th>               
            </tr>
        </thead>        
        <tbody id="tbody">
           {foreach from=$LIST key="mykey" item=user}
           <tr>
            <td>{$user.id}</td> 
            <td>{$user.order_id}</td>
            <td>{$user.realname}</td>
            <td>{$user.idcard}</td>
            <td>{$user.phone}</td>           
            <td>{$user.updated_at}</td>
          
                        
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