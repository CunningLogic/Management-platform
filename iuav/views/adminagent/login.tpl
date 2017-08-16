<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>{$LANGDATA.iuav_login_title}</title>

    <link rel="stylesheet" type="text/css" href="{$CDNCONFIGURL}/public/css/externals/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="{$CDNCONFIGURL}/public/css/login.css"/>
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
                            <label class="col-sm-3 control-label">{$LANGDATA.iuav_user_account}</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="LoginForm[username]" id="username" value="{$username|escape:"html"}"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">{$LANGDATA.iuav_password}</label>
                            <div class="col-sm-9">
                                <input type="password" class="form-control" name="LoginForm[password]" id="password" autocomplete="off"/>
                            </div>
                        </div>

                        {if $CaptchaHtml neq ''}
                        <div class="form-group form-captcha-group">
                            <label class="col-sm-3 control-label">{$LANGDATA.iuav_verification_code}</label>
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="col-sm-5 form-captcha-input-wrap">
                                        <input type="text" class="form-control" name="captcha"/>
                                    </div>
                                    <div class="col-sm-7">
                                        <img id="w0-image" class="captcha-img"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/if}

                        <div class="form-group" style="margin-top: -20px;">
                            <div class="col-sm-offset-3 col-sm-9">
                                <p class="help-block">
                                    <a href="/adminagent/getpassword">{$LANGDATA.iuav_forgot_password}</a>
                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-9">
                                {if $error}
                                    <div class="alert alert-danger">{$error|escape:"html"}</div>
                                {/if}
                                <button type="submit" class="btn btn-default btn-block">{$LANGDATA.iuav_login}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" data-main="/public/js/login.js?v=2" src="/public/js/externals/require-2.1.11.min.js"></script>
    {include "footer.tpl"}
</body>
</html>