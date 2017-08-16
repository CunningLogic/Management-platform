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
<script src="/js/md5.js"></script>
<script type="text/javascript">
    {literal}
    
    var idToSelected = {};
    function onchangeid(id) {       
        idToSelected[id.value] = id.checked;

    }

    function getFromIdToSelected() {
            var arr = [];
            for (var id in idToSelected) {
                if (idToSelected[id]) {
                    arr.push(id);
                }
            }
            return arr;
    }
    function loginSubmit() {  
     var ids = getFromIdToSelected().join(',');
     $("#purviewids").val(ids);
     return true;
        /* 
        var password = $("#loginform-password").val();
        var username = $("#loginform-username").val();
        if (password && username) {
            //alert(username + password);
            password = MD5(username + password);
            $("#loginform-password").val(password);
            return true;
        }
        if (username) {
             return true;
        }        
        //loginform-password
        return false;
        */
    }




    {/literal}

</script>

<div class="site-login">
    <h1>增加权限</h1>

    <p>增加权限</p>

    <form id="login-form" class="form-horizontal" action="/adminuser/addrolepurview" method="post" onsubmit="return loginSubmit();">
        <input type="hidden" name="_csrf" value="{$csrftoken}">
        <input type="hidden" name="id" value="{if $LIST}{$LIST.0.id}{/if}">
        <input type="hidden" name="roleid" value="{$roleid}">
        <input type="hidden" name="purviewids"  id="purviewids" value="">

        <table cellspacing="10" cellpadding="10" border="1" width="100%">
    
        <thead>
            <tr>
                <th >id</th>
                <th >上一级id</th>
                <th >链接名称</th> 
                <th >链接地址</th>
                <th >类名称和方法名</th>
                <th >描述</th>
                <th >是否有权限</th>
                <th >操作</th>               
            </tr>
        </thead>
        
        <tbody id="tbody">
           {foreach from=$listpurview key="mykey" item=user}
           <tr>
            <td>{$user.id}</td>
            <td>{$user.upper_purview_id}</td>
            <td>{$user.redirect_name}</td>
            <td>{$user.redirect_url}</td>
            <td>{$user.method}</td>
            <td>{$user.description}</td> 
            <td>{$user.ishave}</td>
            <td> <input name="LoginForm[havepurview_{$user.id}]" type="checkbox" value="{$user.id}" onchange="onchangeid(this)"/> </td>                          
          </tr>
           {/foreach}
           
        </tbody>
    
        </table>
        
       <div class="form-group">
             <input name="LoginForm[action]" type="radio" value="add" /> 增加 <input name="LoginForm[action]" type="radio" value="delete" /> 删除
            <div class="col-lg-offset-1 col-lg-11">
                <button type="submit" class="btn btn-primary" name="login-button">提交</button>
            </div>
        </div>

    </form>
</div>


    </div>
</div>


{include '../admin/footer.tpl'}

</body>
</html>