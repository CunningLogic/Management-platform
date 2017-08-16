<div class="divtop">
    <div class="div_logo">
        <div class="divlogoimage">
            <a href="/adminvisionary/index"><img src="/images/logo.png" class="img_logo" /></a>
        </div>

        {if isset($title) && $title != ""}
        <div class="div_titleimage">
            {if !isset($imageSrc) || $imageSrc == ""}
                <img src="/images/infopage.png" class="profile-s imgstyle"/>
            {else}
                <a href="/adminvisionary/info/?id={$id}">
                    <img src="{$imageSrc}/270x270" class="profile-s imgstyle"/>
                </a>
            {/if}
            <span class="titlespan">{$title}</span>
        </div>
        {/if}
    </div>
</div>
