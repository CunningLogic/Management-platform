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
    }

    {/literal}

</script>

<div class="site-login">
    <h1>增加后台账号</h1>

    <p>增加后台账号</p>

    <form id="login-form" class="form-horizontal" action="/adminuser/add" method="post" onsubmit="return loginSubmit();">
        <input type="hidden" name="_csrf" value="{$csrftoken}">
        <input type="hidden" name="id" value="{if $LIST}{$LIST.0.id}{/if}">
        <div class="form-group field-loginform-upper_agent_id required">
            <label class="col-lg-1 control-label" for="loginform-upper_agent_id">上级用户id</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-upper_agent_id" class="form-control" name="LoginForm[upper_agent_id]" value="{if $LIST}{$LIST.0.upper_agent_id}{/if}">
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>

        <div class="form-group field-loginform-username required">
            <label class="col-lg-1 control-label" for="loginform-username">Username</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-username" class="form-control" name="LoginForm[username]" value="{if $LIST}{$LIST.0.username}{/if}" >
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>

        <div class="form-group field-loginform-password required">
            <label class="col-lg-1 control-label" for="loginform-password">Password</label>

            <div class="col-lg-3">
                <input type="password" id="loginform-password" class="form-control" name="LoginForm[password]" value="" autocomplete="off"/>
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>
        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">remark</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-remark" class="form-control" name="LoginForm[remark]" value="{if $LIST}{$LIST.0.remark}{/if}">
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>

        <div class="form-group field-loginform-rememberme">
            <div class="checkbox">
                <label for="loginform-rememberme">
                    <input type="hidden" name="LoginForm[rememberMe]" value="0"/>
                    
                </label>

                <p class="help-block help-block-error"></p>

            </div>
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