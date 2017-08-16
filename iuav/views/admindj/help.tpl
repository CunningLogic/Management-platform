<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-param" content="_csrf">
    <meta name="csrf-token" content="{$csrftoken}">
    <title>{$username}</title>
    <link href="/assets/91e176a9/css/bootstrap.css" rel="stylesheet">
<link href="/css/site.css" rel="stylesheet">
<link href="/assets/1975bc1f/toolbar.css" rel="stylesheet"></head>
<body>

<div class="wrap">
    <nav id="w0" class="navbar-inverse navbar-fixed-top navbar" role="navigation"><div class="container"><div class="navbar-header"><button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#w0-collapse"><span class="sr-only">Toggle navigation</span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span></button><a class="navbar-brand" href="/">My Company</a></div><div id="w0-collapse" class="collapse navbar-collapse"><ul id="w1" class="navbar-nav navbar-right nav"><li><a href="/site/index">Home</a></li>
<li><a href="/site/about">About</a></li>
<li><a href="/site/contact">Contact</a></li>
<li><a href="/site/login">Login</a></li></ul></div></div></nav>
    <div class="container">
        <ul class="breadcrumb"><li><a href="/">Home</a></li>
<li class="active">Login111</li>
</ul>        <script src="/js/md5.js"></script>
<script type="text/javascript">
{literal}

function loginSubmit()
{
    

    //alert($("#loginform-password").var() );
    var password = $("#loginform-password").val();
    var username = $("#loginform-username").val();
    
    if (password && username) {
        //alert(username + password);
        password = MD5(username + password);
        $("#loginform-password").val(password);
        return true;
    }
    
    //loginform-password
    return false;
}

{/literal}

</script>

<div class="site-login">
    <h1>Login111</h1>

    <p>Please fill out the following fields to login:</p>

    <form id="login-form" class="form-horizontal" action="/admindj/login" method="post" onsubmit="return loginSubmit();">
<input type="hidden" name="_csrf" value="{$csrftoken}">
    <div class="form-group field-loginform-username required">
<label class="col-lg-1 control-label" for="loginform-username">Username</label>
<div class="col-lg-3"><input type="text" id="loginform-username" class="form-control" name="LoginForm[username]"></div>
<div class="col-lg-8"><p class="help-block help-block-error"></p></div>
</div>

<div class="form-group field-loginform-password required">
<label class="col-lg-1 control-label" for="loginform-password">Password</label>
<div class="col-lg-3"><input type="password" id="loginform-password" class="form-control" name="LoginForm[password]" value="" autocomplete="off"></div>
<div class="col-lg-8"><p class="help-block help-block-error"></p></div>
</div>
    <div class="form-group field-loginform-rememberme">
<div class="checkbox">
<label for="loginform-rememberme">
<input type="hidden" name="LoginForm[rememberMe]" value="0"><input type="checkbox" id="loginform-rememberme" name="LoginForm[rememberMe]" value="1" checked>
Remember Me
</label>
<p class="help-block help-block-error"></p>

</div>
</div>
    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <button type="submit" class="btn btn-primary" name="login-button">Login</button>        </div>
    </div>

    </form>    
</div>


    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; My Company 2015</p>

        <p class="pull-right">Powered by <a href="http://www.yiiframework.com/" rel="external">Yii Framework</a></p>
    </div>
</footer>

<div id="yii-debug-toolbar" data-url="/debug/default/toolbar?tag=565fc133e63ad" style="display:none" class="yii-debug-toolbar-bottom"></div><script src="/assets/12ff054e/jquery.js"></script>
<script src="/assets/49718d62/yii.js"></script>
<script src="/assets/49718d62/yii.validation.js"></script>
<script src="/assets/49718d62/yii.activeForm.js"></script>
<script src="/assets/91e176a9/js/bootstrap.js"></script>
<script src="/assets/1975bc1f/toolbar.js"></script>
<script type="text/javascript">
{literal}
jQuery(document).ready(function () {
jQuery('#login-form').yiiActiveForm([{"id":"loginform-username","name":"username","container":".field-loginform-username","input":"#loginform-username","error":".help-block.help-block-error","validate":function (attribute, value, messages, deferred, $form) {yii.validation.required(value, messages, {"message":"Username cannot be blank."});}},{"id":"loginform-password","name":"password","container":".field-loginform-password","input":"#loginform-password","error":".help-block.help-block-error","validate":function (attribute, value, messages, deferred, $form) {yii.validation.required(value, messages, {"message":"Password cannot be blank."});}},{"id":"loginform-rememberme","name":"rememberMe","container":".field-loginform-rememberme","input":"#loginform-rememberme","error":".help-block.help-block-error","validate":function (attribute, value, messages, deferred, $form) {yii.validation.boolean(value, messages, {"trueValue":"1","falseValue":"0","message":"Remember Me must be either \"1\" or \"0\".","skipOnEmpty":1});}}], []);
});
{/literal}
</script></body>
</html>



