{if isset($id) && $id != ""}
<div class="menu clearfix">
    <div class="menu-inner">
        <a{if $current=="info"} class="current"{/if} href="/adminvisionary/info/?id={$id}">INFO</a>
        <a{if $current=="frontPage"} class="current"{/if} href="frontPage.html">FrontPage</a>
        <a{if $current=="image"} class="current"{/if} href="/adminvisionary/imageindex/?id={$id}">Image</a>
        <a{if $current=="video"} class="current"{/if} href="/adminvisionary/videoindex/?id={$id}">Video</a>
        <a{if $current=="news"} class="current"{/if} href="#">News</a>
    </div>
</div>
{/if}