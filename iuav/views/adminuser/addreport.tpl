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
   
    <h1>处理用户反馈</h1>
    <p>处理用户反馈</p>
   
    <form id="login-form" class="form-horizontal" action="/adminuser/addreport" method="post" onsubmit="return loginSubmit();">
        <input type="hidden" name="_csrf" value="{$csrftoken}">
        <input type="hidden" name="id" value="{if $LIST}{$LIST.0.id}{/if}"> 

        <div class="form-group field-loginform-username required">
            <label class="col-lg-1 control-label" for="loginform-username">类型</label>

            <div class="col-lg-3">
                <select name="LoginForm[type]" disabled="disabled">
                  <option value="agent" {if $LIST}{if $LIST.0.type eq "agent"}selected{/if}{/if} >代理</option>
                  <option value="client" {if $LIST}{if $LIST.0.type eq "product"}selected{/if}{/if} >产品</option>

                </select>               
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>
        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">标题</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-title" class="form-control" name="LoginForm[title]" value="{if $LIST}{$LIST.0.title}{/if}" disabled="disabled">
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>
        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">内容</label>

            <div class="col-lg-3">
                <textarea rows="5" cols="20" name="LoginForm[message]" class="form-control" disabled="disabled" >
                 {if $LIST}{$LIST.0.message}{/if}
                </textarea>
               
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>

        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">历史备注</label>

            <div class="col-lg-3">
                 {if $REMARKLIST}
                     {foreach from=$REMARKLIST key="mykey" item=remarkitem}
                     {$remarkitem.message}<br/>
                     {/foreach}

                 {/if}              
               
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>




        <div class="form-group field-loginform-username required">
            <label class="col-lg-1 control-label" for="loginform-username">状态</label>
            <div class="col-lg-3">
                <select name="LoginForm[status]">
                  <option value="nofollow" {if $LIST}{if $LIST.0.status eq "nofollow"}selected{/if}{/if} >未跟进</option>
                  <option value="beenup" {if $LIST}{if $LIST.0.status eq "beenup"}selected{/if}{/if} >已跟进</option>
                  <option value="completed" {if $LIST}{if $LIST.0.status eq "completed"}selected{/if}{/if} >已完成</option>
                </select>               
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>

         <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">备注</label>

            <div class="col-lg-3">
                <textarea rows="5" cols="20" name="LoginForm[remark]" class="form-control" >
                 
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