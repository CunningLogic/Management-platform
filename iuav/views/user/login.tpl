<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>登陆 - DJI农业机代理商管理平台</title>

    <link rel="stylesheet" type="text/css" href="/public/css/externals/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="/public/css/login.css"/>
</head>
<body>
    <div class="container">

        <div class="logo-wrap">
            <a href="/adminagent/login" class="logo"></a>
        </div>

        <div class="form-wrap">

            <div class="row form login-form">
                <div class="form-head col-sm-offset-3 col-sm-9">
                    <img src="/public/images/logo_login.png" />
                </div>

                <div class="form-body">
                    <form method="post" action="/adminagent/login" class="form-horizontal" id="loginForm">
                        <input type="hidden" name="_csrf" value="{$csrftoken}" ／>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">用户名</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="LoginForm[username]" id="username" value="{$username|escape:"html"}"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">密码</label>
                            <div class="col-sm-9">
                                <input type="password" class="form-control" name="LoginForm[password]" id="password" autocomplete="off"/>
                                <p class="help-block">
                                    <a href="/adminagent/getpassword">忘记密码？</a>
                                </p>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-9">

                                <div role="alert alert-danger">{$error}</div>
                                <button type="submit" class="btn btn-default btn-block">登录</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {*<div class="row form pwd-form">*}
                {*<div class="form-head col-sm-offset-4 col-sm-8">*}
                    {*<h3>设置密码</h3>*}
                {*</div>*}

                {*<div class="form-body">*}
                    {*<form class="form-horizontal">*}
                        {*<div class="form-group">*}
                            {*<label class="col-sm-4 control-label">用户名</label>*}
                            {*<div class="col-sm-8">*}
                                {*<input type="text" class="form-control" />*}
                            {*</div>*}
                        {*</div>*}

                        {*<div class="form-group">*}
                            {*<label class="col-sm-4 control-label">新密码</label>*}
                            {*<div class="col-sm-8">*}
                                {*<input type="text" class="form-control" />*}
                            {*</div>*}
                        {*</div>*}

                        {*<div class="form-group">*}
                            {*<label class="col-sm-4 control-label">确认密码</label>*}
                            {*<div class="col-sm-8">*}
                                {*<input type="text" class="form-control" />*}
                            {*</div>*}
                        {*</div>*}

                        {*<div class="form-group">*}
                            {*<div class="col-sm-offset-4 col-sm-8">*}
                                {*<button type="button" class="btn btn-default btn-block">确认</button>*}
                            {*</div>*}
                        {*</div>*}
                    {*</form>*}
                {*</div>*}
            {*</div>*}
        </div>
    </div>

    <script type="text/javascript" data-main="/public/js/login.js" src="/public/js/externals/require-2.1.11.min.js"></script>
    {include "footer.tpl"}
</body>
</html>