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
        var password = $("#loginform-password").val();
        var email = $("#loginform-email").val();
        var code = $("#loginform-code").val();
        if (!code) {
            alert("代理商code不能为空");
            return false;
        }
        if (password && email) {
            //alert(username + password);
            password = MD5(email + password);
            $("#loginform-password").val(password);
            return true;
        }
        if (email) {
             return true;
        }        
        //loginform-password
        return false;
    }

    {/literal}

</script>

<div class="site-login">
    {if $upper_agent_id eq 0}
    <h1>增加一级代理账号</h1>
    <p>增加一级代理账号</p>
    {else}
         <h1>增加二级代理账号</h1>
          <p>增加二级代理账号</p>
    {/if}
    <form id="login-form" class="form-horizontal" action="/adminuser/addagent" method="post" onsubmit="return loginSubmit();">
        <input type="hidden" name="_csrf" value="{$csrftoken}">
        <input type="hidden" name="id" value="{if $LIST && $from neq 'mis' }{$LIST.0.id}{/if}">
        <input type="hidden" name="LoginForm[upper_agent_id]" value="{$upper_agent_id}">
        <input type="hidden" name="misid" value="{if $LIST && $from eq 'mis' }{$LIST.0.id}{/if}">
       

        <!--div class="form-group field-loginform-username required">
            <label class="col-lg-1 control-label" for="loginform-username">登陆用户名</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-username" class="form-control" name="LoginForm[username]" value="{if $LIST && $from neq 'mis'}{$LIST.0.username}{/if}" >
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div-->

        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">邮箱</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-email" class="form-control" name="LoginForm[email]" value="{if $LIST}{$LIST.0.email}{/if}">
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>


        <div class="form-group field-loginform-password required">
            <label class="col-lg-1 control-label" for="loginform-password">密码</label>

            <div class="col-lg-3">
                <input type="password" id="loginform-password" class="form-control" name="LoginForm[password]" value="" autocomplete="off"/>
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>
        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">代理商名称</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-agentname" class="form-control" name="LoginForm[agentname]" value="{if $LIST}{$LIST.0.agentname}{/if}">
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>
        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">代理商code</label>

            <div class="col-lg-3">
             <input type="text" id="loginform-code" class="form-control" name="LoginForm[code]" value="{if $LIST}{$LIST.0.code}{/if}">
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>

        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">代理商oldcode</label>

            <div class="col-lg-3">
               <input type="text" id="loginform-oldcode" class="form-control" name="LoginForm[oldcode]" value="{if $LIST}{$LIST.0.oldcode}{/if}">
           
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>

        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">负责人</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-realname" class="form-control" name="LoginForm[realname]" value="{if $LIST}{$LIST.0.realname}{/if}">
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
                <select name="LoginForm[country]" class="form-control">
                    <option value="cn" {if $LIST}{if $LIST.0.country eq 'cn'}selected{/if}{/if}>China</option>
                    <option value="at" {if $LIST}{if $LIST.0.country eq 'at'}selected{/if}{/if} >Austria</option>
                    <option value="am" {if $LIST}{if $LIST.0.country eq 'am'}selected{/if}{/if} >Armenia</option>
                    <option value="ar" {if $LIST}{if $LIST.0.country eq 'ar'}selected{/if}{/if} >Argentina</option>
                    <option value="au" {if $LIST}{if $LIST.0.country eq 'au'}selected{/if}{/if} >Australia</option>
                    <option value="be" {if $LIST}{if $LIST.0.country eq 'be'}selected{/if}{/if}>Belgium</option>
                    <option value="bg" {if $LIST}{if $LIST.0.country eq 'bg'}selected{/if}{/if}>Bulgaria</option>
                    <option value="br" {if $LIST}{if $LIST.0.country eq 'br'}selected{/if}{/if}>Brazil</option>
                    <option value="ca" {if $LIST}{if $LIST.0.country eq 'ca'}selected{/if}{/if}>Canada</option>
                    <option value="co" {if $LIST}{if $LIST.0.country eq 'co'}selected{/if}{/if}>Colombia</option>
                    <option value="cl" {if $LIST}{if $LIST.0.country eq 'cl'}selected{/if}{/if}>Chile</option>

                    <option value="hr" {if $LIST}{if $LIST.0.country eq 'hr'}selected{/if}{/if}>Croatia</option>
                    <option value="cy" {if $LIST}{if $LIST.0.country eq 'cy'}selected{/if}{/if}>Cyprus</option>
                    <option value="cz" {if $LIST}{if $LIST.0.country eq 'cz'}selected{/if}{/if}>Czech Republic</option>
                    <option value="dk" {if $LIST}{if $LIST.0.country eq 'dk'}selected{/if}{/if}>Denmark</option>
                    <option value="do" {if $LIST}{if $LIST.0.country eq 'do'}selected{/if}{/if}>Dominican Republic</option>
                    
                    <option value="ee" {if $LIST}{if $LIST.0.country eq 'ee'}selected{/if}{/if}>Estonia</option>
                    <option value="fi" {if $LIST}{if $LIST.0.country eq 'fi'}selected{/if}{/if}>Finland</option>
                    <option value="fr" {if $LIST}{if $LIST.0.country eq 'fr'}selected{/if}{/if} >France</option>
                    <option value="de" {if $LIST}{if $LIST.0.country eq 'de'}selected{/if}{/if}>Germany</option>
                    <option value="gr" {if $LIST}{if $LIST.0.country eq 'gr'}selected{/if}{/if}>Greece</option>
                    <option value="hk" {if $LIST}{if $LIST.0.country eq 'hk'}selected{/if}{/if}>Hong Kong</option>
                    <option value="hu" {if $LIST}{if $LIST.0.country eq 'hu'}selected{/if}{/if}>Hungary</option>
                    <option value="ie" {if $LIST}{if $LIST.0.country eq 'ie'}selected{/if}{/if}>Ireland</option>
                    <option value="it" {if $LIST}{if $LIST.0.country eq 'it'}selected{/if}{/if}>Italy</option>
                    <option value="jp" {if $LIST}{if $LIST.0.country eq 'jp'}selected{/if}{/if}>Japan</option>
                    <option value="lv" {if $LIST}{if $LIST.0.country eq 'lv'}selected{/if}{/if}>Latvia</option>
                    <option value="li" {if $LIST}{if $LIST.0.country eq 'li'}selected{/if}{/if}>Liechtenstein</option>
                    <option value="lt" {if $LIST}{if $LIST.0.country eq 'lt'}selected{/if}{/if}>Lithuania</option>
                    <option value="lu" {if $LIST}{if $LIST.0.country eq 'lu'}selected{/if}{/if}>Luxembourg</option>
                    <option value="mo" {if $LIST}{if $LIST.0.country eq 'mo'}selected{/if}{/if}>Macau</option>
                    <option value="my" {if $LIST}{if $LIST.0.country eq 'my'}selected{/if}{/if}>Malaysia</option>
                    <option value="mt" {if $LIST}{if $LIST.0.country eq 'mt'}selected{/if}{/if}>Malta</option>
                    <option value="mx" {if $LIST}{if $LIST.0.country eq 'mx'}selected{/if}{/if}>Mexico</option>
                    <option value="mc" {if $LIST}{if $LIST.0.country eq 'mc'}selected{/if}{/if}>Monaco</option>
                    <option value="nl" {if $LIST}{if $LIST.0.country eq 'nl'}selected{/if}{/if}>Netherlands</option>
                    <option value="nz" {if $LIST}{if $LIST.0.country eq 'nz'}selected{/if}{/if}>New Zealand</option>
                    <option value="no" {if $LIST}{if $LIST.0.country eq 'no'}selected{/if}{/if}>Norway</option>
                    <option value="pl" {if $LIST}{if $LIST.0.country eq 'pl'}selected{/if}{/if}>Poland</option>
                    <option value="pt" {if $LIST}{if $LIST.0.country eq 'pt'}selected{/if}{/if}>Portugal</option>
                    <option value="pr" {if $LIST}{if $LIST.0.country eq 'pr'}selected{/if}{/if}>Puerto Rico</option>
                    <option value="ro" {if $LIST}{if $LIST.0.country eq 'ro'}selected{/if}{/if}>Romania</option>
                    <option value="ru" {if $LIST}{if $LIST.0.country eq 'ru'}selected{/if}{/if}>Russian</option>
                    <option value="sg" {if $LIST}{if $LIST.0.country eq 'sg'}selected{/if}{/if}>Singapore</option>
                    <option value="sk" {if $LIST}{if $LIST.0.country eq 'sk'}selected{/if}{/if}>Slovakia</option>
                    <option value="si" {if $LIST}{if $LIST.0.country eq 'si'}selected{/if}{/if}>Slovenia</option>
                    <option value="kr" {if $LIST}{if $LIST.0.country eq 'kr'}selected{/if}{/if}>South Korea</option>
                    <option value="es" {if $LIST}{if $LIST.0.country eq 'es'}selected{/if}{/if}>Spain</option>
                    <option value="se" {if $LIST}{if $LIST.0.country eq 'se'}selected{/if}{/if}>Sweden</option>
                    <option value="ch" {if $LIST}{if $LIST.0.country eq 'ch'}selected{/if}{/if}>Switzerland</option>
                    <option value="tw" {if $LIST}{if $LIST.0.country eq 'tw'}selected{/if}{/if}>Taiwan</option>
                    <option value="th" {if $LIST}{if $LIST.0.country eq 'th'}selected{/if}{/if}>Thailand</option>
                    <option value="gb" {if $LIST}{if $LIST.0.country eq 'gb'}selected{/if}{/if}>United Kingdom</option>
                    <option value="ua" {if $LIST}{if $LIST.0.country eq 'ua'}selected{/if}{/if}>Ukraine</option>
                    <option value="us" {if $LIST}{if $LIST.0.country eq 'us'}selected{/if}{/if}>United States</option>
                </select>
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
            <label class="col-lg-1 control-label" for="loginform-remark">地址</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-address" class="form-control" name="LoginForm[address]" value="{if $LIST}{$LIST.0.address}{/if}">
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>
         <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">DJI负责人</label>

            <div class="col-lg-3">
                <input type="text" id="loginform-staff" class="form-control" name="LoginForm[staff]" value="{if $LIST}{$LIST.0.staff}{/if}">
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>

        
        <div class="form-group field-loginform-remark required">
            <label class="col-lg-1 control-label" for="loginform-remark">是否购买保险</label>

            <div class="col-lg-3">               
                <select id="loginform-is_policies" class="form-control"  name="LoginForm[is_policies]">
                  <option value ="1" {if $LIST}{if $LIST.0.is_policies eq 1}selected{/if}{/if}>购买</option>
                  <option value ="0" {if $LIST}{if  $from neq 'mis' and $LIST.0.is_policies eq 0 }selected{/if}{/if}>不购买</option>
                 
                  </select>
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>

        <div class="form-group field-loginform-inside required">
            <label class="col-lg-1 control-label" for="loginform-inside">是否内部</label>

            <div class="col-lg-3">               
                <select id="loginform-is_policies" class="form-control"  name="LoginForm[inside]">
                  <option value ="0" {if $LIST}{if  $from neq 'mis' and $LIST.0.inside eq 0}selected{/if}{/if}>不是</option>
                  <option value ="1" {if $LIST}{if  $from neq 'mis' and $LIST.0.inside eq 1}selected{/if}{/if}>是</option>                
                 
                  </select>
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