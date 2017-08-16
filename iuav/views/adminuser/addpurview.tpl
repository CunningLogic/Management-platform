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
    <h1>增加访问地址</h1>

    <p>增加访问地址          <a href='/adminuser/listpurview/'>访问地址列表</a></p>

    <form id="login-form" class="form-horizontal" action="/adminuser/addpurview" method="post" onsubmit="return loginSubmit();">
        <input type="hidden" name="_csrf" value="{$csrftoken}">
        <input type="hidden" name="id" value="{if $LIST}{$LIST.0.id}{/if}">
         <div class="form-group field-loginform-name required">
            <label class="col-lg-1 control-label" for="loginform-name">上一级id</label>

            <div class="col-lg-3">
                <select id="loginform-upper_purview_id"  name="LoginForm[upper_purview_id]">
                    <option value="0" {if $LIST} {if $LIST.0.upper_purview_id eq '0'  }selected{/if} {/if}  >无</option>
                {foreach from=$listPurview key="mykey" item=purview}
                     <option value="{$purview.id}" {if $LIST} {if $purview.id eq $LIST.0.upper_purview_id  }selected{/if} {/if}  >{$purview.redirect_name}</option>
                {/foreach}
                </select>
               
            </div>
            
        </div>

        <div class="form-group field-loginform-name required">
            <label class="col-lg-1 control-label" for="loginform-name">链接名称</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-redirect_name" class="form-control" name="LoginForm[redirect_name]" value="{if $LIST}{$LIST.0.redirect_name}{/if}" >
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>

        <div class="form-group field-loginform-password required">
            <label class="col-lg-1 control-label" for="loginform-password">链接地址</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-redirect_url" class="form-control" name="LoginForm[redirect_url]" value="{if $LIST}{$LIST.0.redirect_url}{/if}" autocomplete="off"/>
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>

        <div class="form-group field-loginform-password required">
            <label class="col-lg-1 control-label" for="loginform-password">类名称和方法名</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-method" class="form-control" name="LoginForm[method]" value="{if $LIST}{$LIST.0.method}{/if}" autocomplete="off"/>
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>

        <div class="form-group field-loginform-password required">
            <label class="col-lg-1 control-label" for="loginform-password">描述</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-description" class="form-control" name="LoginForm[description]" value="{if $LIST}{$LIST.0.description}{/if}" autocomplete="off"/>
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