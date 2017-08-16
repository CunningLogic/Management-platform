<div class="left-side">
    <div class="logo-wrap">
        <a href="/user" class="logo">
            <span class="logo-icon"></span>
        </a>
    </div>

    <ul class="nav">
        <li>
            <a href="/user"{if $nav == "management"} class="active"{/if}><i class="icon icon-2"></i>我的飞行器</a>
        </li>
        {if $tpl_yii_env eq 'dev'}  
        <li>
            <a href="/user/maintaince/"{if $nav == "maintaince"} class="active"{/if}><i class="icon icon-4"></i>维修系统</a>
        </li>
        {/if}
        <li>
            <a href="/user/feedback"{if $nav == "feedback"} class="active"{/if}><i class="icon icon-6"></i>信息反馈</a>
        </li>
        {*<li>*}
            {*<a href="###"><i class="icon icon-5"></i>大疆公告</a>*}
        {*</li>*}
    </ul>
</div>