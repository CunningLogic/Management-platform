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
    function loginSubmit() {    
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
    <h1>修改用户角色</h1>

    <p>修改用户角色</p>

    <form id="login-form" class="form-horizontal" action="/adminuser/adduserrole" method="post" onsubmit="return loginSubmit();">
        <input type="hidden" name="_csrf" value="{$csrftoken}">
       
        <div class="form-group field-loginform-name required">
            <label class="col-lg-1 control-label" for="loginform-name">用户名</label>

            <div class="col-lg-3">
                {if $listuser}
                 <select id="loginform-upper_purview_id"  name="LoginForm[user_id]">
                   
                {foreach from=$listuser key="mykey" item=useritme}
                     <option value="{$useritme.id}"   >{$useritme.username}</option>
                {/foreach}
                </select>
                {/if}
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>

        <div class="form-group field-loginform-password required">
            <label class="col-lg-1 control-label" for="loginform-password">角色</label>
            <div class="col-lg-3">
                <select id="loginform-upper_purview_id"  name="LoginForm[role_id]">
                    <option value="0" {if $listuser} {if $listuser.0.role_id eq '0'  }selected{/if} {/if}  >无</option>
                {foreach from=$listrole key="mykey" item=role}
                     <option value="{$role.id}" {if $listuser} {if $role.id eq $listuser.0.role_id  }selected{/if} {/if}  >{$role.name}</option>
                {/foreach}
                </select>

              
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>
       <div class="form-group">
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