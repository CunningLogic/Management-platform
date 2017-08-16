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
    <nav id="w0" class="navbar-inverse navbar-fixed-top navbar" role="navigation"><div class="container"><div class="navbar-header"><button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#w0-collapse"><span class="sr-only">Toggle navigation</span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span></button><a class="navbar-brand" href="/">DJI</a></div><div id="w0-collapse" class="collapse navbar-collapse"><ul id="w1" class="navbar-nav navbar-right nav"><li><a href="/adminuser/list">用户列表</a></li>
<li class="active"><a href="/admin/">Login</a></li></ul></div></div></nav>
    <div class="container">

<script src="/js/md5.js"></script>
<script type="text/javascript">
    {literal}
    function loginSubmit() {     
        var oldpassword = $("#loginform-oldpassword").val();
        var newpassword = $("#loginform-newpassword").val();
        var retpassword = $("#loginform-retpassword").val();
        if (retpassword != newpassword) {
           alert("新密码2次输入不一致");
           return false;
        }
        var username = $("#loginform-username").val();
        if (oldpassword && username && newpassword && retpassword ) {
            //alert(username + password);
            oldpassword = MD5(username + oldpassword);
            $("#loginform-oldpassword").val(oldpassword);
            newpassword = MD5(username + newpassword);
            $("#loginform-newpassword").val(newpassword);
            retpassword = MD5(username + retpassword);
            $("#loginform-retpassword").val(retpassword);            
            return true;
        }
       
        //loginform-password
        return false;
    }

    {/literal}

</script>

<div class="site-login">
    <h1>修改密码</h1>

    <p>修改密码</p>

    <form id="login-form" class="form-horizontal" action="/adminuser/password" method="post" onsubmit="return loginSubmit();">
        <input type="hidden" name="_csrf" value="{$csrftoken}">
        <input type="hidden" name="loginform-username" id="loginform-username" value="{$username}">
       <div class="form-group field-loginform-oldpassword required">
            <label class="col-lg-1 control-label" for="loginform-oldpassword">当前密码</label>

            <div class="col-lg-3">
                <input type="password" id="loginform-oldpassword" class="form-control" name="LoginForm[oldpassword]" value="" autocomplete="off"/>
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error">{$error_oldpassword}</p></div>
        </div>
        <div class="form-group field-loginform-newpassword required">
            <label class="col-lg-1 control-label" for="loginform-newpassword">新密码</label>

            <div class="col-lg-3">
                <input type="password" id="loginform-newpassword" class="form-control" name="LoginForm[newpassword]" value="" autocomplete="off"/>
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error">{$error_newpassword}</p></div>
        </div>
        <div class="form-group field-loginform-retpassword required">
            <label class="col-lg-1 control-label" for="loginform-retpassword">重复新密码</label>

            <div class="col-lg-3">
                <input type="password" id="loginform-retpassword" class="form-control" name="LoginForm[retpassword]" value="" autocomplete="off"/>
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error">{$error_retpassword}</p></div>
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