{extends file="parent:backend/blauband_email/send.tpl"}

{block name="mail-send--buttons--back-button"}
    {if !empty($mailId)}
        <button id="back-button"
                data-url="{url controller="BlaubandOverview" action="index"}">
            {s namespace="blauband/mail" name="back"}{/s}
        </button>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}