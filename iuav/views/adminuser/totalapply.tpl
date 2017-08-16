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
   
    <h1>统计列表</h1>

    <p>统计列表&nbsp;&nbsp;<a href='/adminuser/totalapply/'>统计列表</a></p>

    <p>
        <form action="/adminuser/totalapply/" method="post" >
        <input type="hidden" name="_csrf" value="{$csrftoken}">
        <div style="font-size:12px;">&nbsp;月份&nbsp;&nbsp;<input type="text" value="{$month|escape:"html"}" id="month" name="month" style="display:inline;">&nbsp;&nbsp;开始日期&nbsp;<input type="date" value="{$begin|escape:"html"}" id="begin" name="begin" style="display:inline;">&nbsp;截止日期&nbsp;<input type="date" value="{$end|escape:"html"}" id="end" name="end" style="display:inline;">&nbsp;&nbsp;<input type="submit" style="display:inline;" value="查询" > &nbsp;&nbsp;
         今日已激活数:{$todayCount}
        </div> 
       </form>
    </p>

      <table cellspacing="10" cellpadding="10" border="1" width="100%">
    
        <thead>
            <tr>
                <th style="cursor:point;" >排名</th>
                <th style="cursor:point;" >代理商</th>
                <th style="cursor:point;" >DJI负责人</th>
                <th style="cursor:point;" >进货数</th>
                <th style="cursor:point;" >激活数</th>
                <th style="cursor:point;">库存数（进货－激活数）</th>               
            </tr>
        </thead>        
        <tbody id="tbody">
           {foreach from=$bodyData key="mykey" item=user}
           <tr>
            <td>{$mykey+1}</td>
            <td>{$user.agentname}</td>
            <td>{$user.staff}</td>
            <td>{$user.total_mon}</td>
            <td>{$user.activenum}</td>
            <td>{$user.stocknum}</td>                        
          </tr>
           {/foreach}           
        </tbody>
    
    </table>

    <br/>

    <table cellspacing="10" cellpadding="10" border="1" width="100%">    
        <thead>
            <tr>
                <th style="cursor:point;" >排名</th>
                <th style="cursor:point;" >代理商</th>
                <th style="cursor:point;" >激活数</th>                           
            </tr>
        </thead>        
        <tbody id="tbody">
           {assign var=activeTotal value="0"}
           {foreach from=$activeData key="mykey" item=user}
           <tr>
            <td>{$mykey+1}</td>
            <td>{$user.agentname}</td>
            <td>{$user.total_mon}</td>  
            {assign var=activeTotal value=$activeTotal+$user.total_mon}                                 
          </tr>
           {/foreach}  
            <tr>
                <th>合计</th>
                <th> </th>
                <th>{$activeTotal}</th>
                           
            </tr>         
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