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
    <h1>增加角色</h1>

    <p>增加角色</p>

    <form id="login-form" class="form-horizontal" action="/adminuser/addrole" method="post" onsubmit="return loginSubmit();">
        <input type="hidden" name="_csrf" value="{$csrftoken}">
        <input type="hidden" name="id" value="{if $LIST}{$LIST.0.id}{/if}">
        <div class="form-group field-loginform-name required">
            <label class="col-lg-1 control-label" for="loginform-name">角色名称</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-name" class="form-control" name="LoginForm[name]" value="{if $LIST}{$LIST.0.name}{/if}" >
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>

        <div class="form-group field-loginform-password required">
            <label class="col-lg-1 control-label" for="loginform-password">sort_order</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-sort_order" class="form-control" name="LoginForm[sort_order]" value="{if $LIST}{$LIST.0.sort_order}{/if}" autocomplete="off"/>
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