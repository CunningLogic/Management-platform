<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-param" content="_csrf">
    <meta name="csrf-token" content="{$csrftoken}">
    <title></title>
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
        var activation = $("#loginform-activation").val();
        if (activation) {
            var hardware_id = $("#loginform-hardware_id").val();
            if (hardware_id) {
                var body_code = $("#loginform-body_code").val();
                if (body_code) {
                    

                }else{
                    alert('整机序列号不能为空');
                    return false; 
                }

            }else{
                alert('硬件id不能为空');
                return false; 
            }
            return true;
        }else{
             alert('激活码不能为空');
            return false;
        }
        
    }

    {/literal}

</script>

<div class="site-login">
    <h1>增加农业机激活码</h1>

    <p>增加农业机激活码</p>

    <form id="login-form" class="form-horizontal" action="/adminsn/add" method="post" onsubmit="return loginSubmit();">
        <input type="hidden" name="_csrf" value="{$csrftoken}">
        <input type="hidden" name="id" value="{if $LIST}{$LIST.0.id}{/if}">
         <div class="form-group field-loginform-type required">
            <label class="col-lg-1 control-label" for="loginform-type">型号</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-type" class="form-control" name="LoginForm[type]" value="{if $LIST}{$LIST.0.type}{/if}" >
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>

        <div class="form-group field-loginform-body_code required">
            <label class="col-lg-1 control-label" for="loginform-body_code">整机序列号</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-body_code" class="form-control" name="LoginForm[body_code]" value="{if $LIST}{$LIST.0.body_code}{/if}">
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>

        <div class="form-group field-loginform-hardware_id required">
            <label class="col-lg-1 control-label" for="loginform-hardware_id">硬件id</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-hardware_id" class="form-control" name="LoginForm[hardware_id]" value="{if $LIST}{$LIST.0.hardware_id}{/if}" >
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>
        <div class="form-group field-loginform-activation required">
            <label class="col-lg-1 control-label" for="loginform-activation">激活码</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-activation" class="form-control" name="LoginForm[activation]" value="{if $LIST}{$LIST.0.activation}{/if}" >
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>  

        <div class="form-group field-loginform-scan_date required">
            <label class="col-lg-1 control-label" for="loginform-scan_date">扫描日期</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-scan_date" class="form-control" name="LoginForm[scan_date]" value="{if $LIST}{$LIST.0.scan_date}{/if}">
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

        <p class="pull-right">Powered by <a href="http://www.yiiframework.com/" rel="external">DJI</a></p>
    </div>
</footer>
<script src="/js/jquery.js"></script>
<script src="/js/bootstrap.js"></script>
</body>
</html>