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
        return true; 
       
    }

    {/literal}

</script>

<div class="site-login">
   
    <h1>修改激活人员信息</h1>
    <p>修改激活人员信息</p>
   
    <form id="login-form" class="form-horizontal" action="/adminuser/addapply" method="post">
        <input type="hidden" name="_csrf" value="{$csrftoken}">
        <input type="hidden" name="id" value="{if $LIST}{$LIST.0.id}{/if}">
         <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">DJI账号</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-account" class="form-control" name="LoginForm[account]" value="{if $LIST}{$LIST.0.account}{/if}">
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div> 
        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">真实用户名</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-realname" class="form-control" name="LoginForm[realname]" value="{if $LIST}{$LIST.0.realname}{/if}">
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div> 
        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">证件号码</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-idcard" class="form-control" name="LoginForm[idcard]" value="{if $LIST}{$LIST.0.idcard}{/if}">
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>
        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">手机号码</label>

            <div class="col-lg-3">
             <input type="text" id="loginform-phone" class="form-control" name="LoginForm[phone]" value="{if $LIST}{$LIST.0.phone}{/if}">
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>

              
        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">国家</label>

            <div class="col-lg-3">
               <input type="text" id="loginform-country" class="form-control" name="LoginForm[country]" value="{if $LIST}{$LIST.0.country}{/if}">   
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>
        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">省份</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-province" class="form-control" name="LoginForm[province]" value="{if $LIST}{$LIST.0.province}{/if}">
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>
        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">城市</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-city" class="form-control" name="LoginForm[city]" value="{if $LIST}{$LIST.0.city}{/if}">
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>
         <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">区</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-area" class="form-control" name="LoginForm[area]" value="{if $LIST}{$LIST.0.area}{/if}">
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>
         <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">街道</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-street" class="form-control" name="LoginForm[street]" value="{if $LIST}{$LIST.0.street}{/if}">
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>

        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">地址</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-address" class="form-control" name="LoginForm[address]" value="{if $LIST}{$LIST.0.address}{/if}">
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

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; DJI 2016</p>

        <p class="pull-right">Powered by <a href="http://www.dji.com/" rel="external">DJI</a></p>
    </div>
</footer>
<script src="/js/jquery.js"></script>
<script src="/js/bootstrap.js"></script>
</body>
</html>