<div class="left-side">
    <div class="logo-wrap">
        <a href="/adminagent/" class="logo">
            <span class="logo-icon"></span>
            <span class="logo-title">{$LANGDATA.iuav_title}</span>
        </a>
    </div>

    <ul class="nav">
        <li>
            <a href="/adminagent/"{if $nav == "active"} class="active"{/if}><i class="icon icon-1"></i>{$LANGDATA.iuav_active_aircraft}</a>
        </li>
        
        {if $UPPERAGENTID eq '0' }
        <li>
            <a href="/adminagent/management"{if $nav == "management"} class="active"{/if}><i class="icon icon-2"></i>{$LANGDATA.iuav_user_management}</a>
        </li>
        
        <li>
            <a href="/adminagent/account"{if $nav == "account"} class="active"{/if}><i class="icon icon-3"></i>{$LANGDATA.iuav_manage_account}</a>
        </li>
        {/if}
        
        {if $tpl_yii_env eq 'dev'}
        <li>
            <a href="/adminagent/aftermarket/" target="_blank"><i class="icon icon-4"></i>{$LANGDATA.iuav_maintenance_system}</a>
        </li>
        {/if}

        {*<li>*}
            {*<a href="/adminagent/feedback"{if $nav == "feedback"} class="active"{/if}><i class="icon icon-6"></i>系统反馈</a>*}
        {*</li>*}
        {*<li>*}
            {*<a href="###"><i class="icon icon-5"></i>大疆公告</a>*}
        {*</li>*}
    </ul>
</div>