<div class="top">
    {$LANGDATA.iuav_lang_select}:
    <select id="countrySelect">
        <option value="cn"{if $country eq 'cn'} selected{/if}>中文</option>
        <option value="us"{if $country eq 'us'} selected{/if}>English</option>
        <option value="kr"{if $country eq 'kr'} selected{/if}>한국어</option>
        <option value="jp"{if $country eq 'jp'} selected{/if}>日本語</option>
        
       
    </select>

    <div class="user-info">
        <span>Hi, {$AGENTNAME}</span>
        <a href="/adminagent/logout" class="logout">{$LANGDATA.iuav_exit}</a>
    </div>
</div>

<script type="text/javascript">
    window.CURRENT_COUNTRY = '{$country}';
    window.LANGUAGE_DATA = {$LANGDATA|json_encode};
</script>

<script type="text/javascript">
    (function() {
        var countrySelect = document.getElementById('countrySelect');
        countrySelect.onchange = function() {
            var country = this.value;
            var pathname = location.pathname;
            pathname = pathname.replace(/\/(cn|us|kr|jp)/i, '');

            location.href = '/' + country + pathname;
        };
    })();
</script>