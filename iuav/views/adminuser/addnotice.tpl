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
      
    }

    {/literal}

</script>

<div class="site-login">
   
    <h1>增加通知</h1>
    <p>增加通知</p>
   
    <form id="login-form" class="form-horizontal" action="/adminuser/addnotice" method="post" onsubmit="return loginSubmit();">
        <input type="hidden" name="_csrf" value="{$csrftoken}">
        <input type="hidden" name="id" value="{if $LIST}{$LIST.0.id}{/if}"> 

        <div class="form-group field-loginform-username required">
            <label class="col-lg-1 control-label" for="loginform-username">类型</label>

            <div class="col-lg-3">
                <select name="LoginForm[type]" >
                  <option value="agent" {if $LIST}{if $LIST.0.type eq "agent"}selected{/if}{/if} >代理</option>
                  <option value="client" {if $LIST}{if $LIST.0.type eq "client"}selected{/if}{/if} >客户</option>

                </select>               
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>
        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">标题</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-title" class="form-control" name="LoginForm[title]" value="{if $LIST}{$LIST.0.title}{/if}">
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>
        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">内容</label>

            <div class="col-lg-3">
                <textarea rows="5" cols="20" name="LoginForm[content]" class="form-control" >
                 {if $LIST}{$LIST.0.content}{/if}
                </textarea>
               
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