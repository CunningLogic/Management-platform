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
        return true;
        var activation = $("#loginform-code").val();
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
             alert('代理code不能为空');
            return false;
        }
        
    }

    {/literal}

</script>

<div class="site-login">
    <h1>保险订单财务标记</h1>

    <p><a href='/adminuser/listpolicies/'>保险列表</a>&nbsp;&nbsp;<a href='/adminuser/listpolicies/?pol_no=null'>保险单为空</a>&nbsp;&nbsp;<a href='/adminuser/nopolicies/'>异常列表</a>&nbsp;&nbsp;<a href='/adminexcel/checkpolicy'>保险订单核对</a>&nbsp;<a href='/adminexcel/markpolicy'>保险订单财务标记</a></p>

    <form id="login-form" class="form-horizontal" action="/adminexcel/markpolicy" method="post" enctype="multipart/form-data" onsubmit="return loginSubmit();" >
        <input type="hidden" name="_csrf" value="{$csrftoken}">   

        <div class="form-group field-loginform-type required">
            <label class="col-lg-1 control-label" for="loginform-type">保险公司xls文件</label>

            <div class="col-lg-3">
                <input type="file" id="xlsfile" name="xlsfile" value="" >
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div>
        
         <!--div class="form-group field-loginform-type required">
            <label class="col-lg-1 control-label" for="loginform-type">是否标识</label>

            <div class="col-lg-3">
                 <input type="checkbox" id="act" name="act" value="1" >
            </div>
            <div class="col-lg-8"><p class="help-block help-block-error"></p></div>
        </div-->        
        
        <div class="form-group">
            <div class="col-lg-offset-1 col-lg-11">
                <button type="submit" class="btn btn-primary" name="login-button">提交</button>
            </div>
        </div>
    </form>
    
    {if $listNoFind }
    <p>异常找不到数据,共{$listNoFindCount}</p>
    <table cellspacing="10" cellpadding="10" border="1" width="100%">    
        <thead>
            <tr>
                <th style="cursor:point;" >&nbsp;</th>
                <th style="cursor:point;" >保单号</th>
                <th style="cursor:point;" >订单号</th>
                <th style="cursor:point;" >证件号</th>
                <th style="cursor:point;" >被保险人姓名</th>
                <th style="cursor:point;" >保单生成时间</th>                           
            </tr>
        </thead>        
        <tbody id="tbody">
           {foreach from=$listNoFind key="mykey" item=user}
           <tr>
            <td>{$user.A}</td>           
            <td><a href="/adminuser/listpolicies/?pol_no={$user.B}" target="_blank">{$user.B}</a></td>
            <td><a href="/adminuser/listpolicies/?order_id={$user.C}" target="_blank">{$user.C}</a></td>
            <td>{$user.D}</td>
            <td>{$user.E}</td>
            <td>{$user.F}</td> 
                        
          </tr>
           {/foreach}           
        </tbody>
    
    </table>
    {/if}
    
    {if $listRepeat }
    <p>重复数据</p>
    <table cellspacing="10" cellpadding="10" border="1" width="100%">    
        <thead>
            <tr>
                <th style="cursor:point;" >&nbsp;</th>
                <th style="cursor:point;" >保单号</th>
                <th style="cursor:point;" >订单号</th>
                <th style="cursor:point;" >证件号</th>
                <th style="cursor:point;" >被保险人姓名</th>
                <th style="cursor:point;" >保单生成时间</th>                           
            </tr>
        </thead>        
        <tbody id="tbody">
           {foreach from=$listRepeat key="mykey" item=user}
           <tr>
            <td>{$user.A}</td>           
            <td><a href="/adminuser/listpolicies/?pol_no={$user.B}" target="_blank">{$user.B}</a></td>
            <td><a href="/adminuser/listpolicies/?order_id={$user.C}" target="_blank">{$user.C}</a></td>
            <td>{$user.D}</td>
            <td>{$user.E}</td>
            <td>{$user.F}</td> 
                        
          </tr>
           {/foreach}           
        </tbody>    
    </table>
    {/if}
    {if $listFind }
      <p>匹配到{$listFindCount}</p>
    {/if}


   

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