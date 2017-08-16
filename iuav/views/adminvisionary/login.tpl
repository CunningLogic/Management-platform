<script src="/js/md5.js"></script>
<script type="text/javascript">
    {literal}

    function loginSubmit() {


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
    <h1>Login</h1>

    <p>Please fill out the following fields to login:</p>

    <form id="login-form" class="form-horizontal" action="/admin/login" method="post" onsubmit="return loginSubmit();">
        <input type="hidden" name="_csrf" value="{$csrftoken}">

        <div class="form-group field-loginform-username required">
            <label class="col-lg-1 control-label" for="loginform-username">Username</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-username" class="form-control" name="LoginForm[username]">
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
        <div class="form-group field-loginform-rememberme">
            <div class="checkbox">
                <label for="loginform-rememberme">
                    <input type="hidden" name="LoginForm[rememberMe]" value="0"/>
                    <input type="checkbox" id="loginform-rememberme" name="LoginForm[rememberMe]" value="1" checked/>
                    Remember Me
                </label>

                <p class="help-block help-block-error"></p>

            </div>
        </div>
        <div class="form-group">
            <div class="col-lg-offset-1 col-lg-11">
                <button type="submit" class="btn btn-primary" name="login-button">Login</button>
            </div>
        </div>

    </form>
</div>





