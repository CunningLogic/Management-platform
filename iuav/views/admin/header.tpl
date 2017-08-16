<nav id="w0" class="navbar-inverse navbar-fixed-top navbar" role="navigation"><div class="container"><div class="navbar-header"><button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#w0-collapse"><span class="sr-only">Toggle navigation</span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span></button><a class="navbar-brand" href="/">DJI</a></div>
<div id="w0-collapse" class="collapse navbar-collapse"><ul id="w1" class="navbar-nav navbar-right nav">



{if $isGuest}
<li class="active"><a href="/admin/">Login</a></li>
{else}
  {if $headerrolePurvieData}
      {foreach from=$headerrolePurvieData key="mykey" item=user}
        <li><a href="{$user.redirect_url}">{$user.redirect_name}</a></li>
      {/foreach}
  {else}
    <li><a href="/adminuser/listagentpending/">申请审批</a></li>
    <li><a href="/adminuser/listagent/">代理用户</a></li>
    <li><a href="/adminsn/listbody">sn和代理</a></li>
    <li><a href="/adminuser/listapply/">已激活</a></li>
    <li><a href="/adminuser/findapply/">售后</a></li>
    <li><a href='/adminuser/totalapply/'>统计</a></li>
    <li><a href="/adminuser/listpolicies/">保险</a></li>
    <li><a href="/adminuser/listreport/">反馈</a></li>

  {/if}




   {if $sessionUserName eq "weiping.huang@dji.com"}   
    <li><a href="/adminuser/listnotice/">发布通知</a></li>
    <li><a href="/adminuser/listpurview/">增加访问</a></li> <li><a href="/adminuser/listrole">角色</a></li> <li><a href="/adminuser/list">后台用户</a></li> 
   {/if}
   <li class="active"><a href="/admin/logout">logout({$sessionUserName})</a></li>
{/if}
</ul></div></div></nav>
