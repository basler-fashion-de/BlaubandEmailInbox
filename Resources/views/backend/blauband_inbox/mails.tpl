{extends file="backend/blauband_email/mails.tpl"}

{block name="mail-list--header--content"}
    {$smarty.block.parent}

    {if $mail->getOrder()}
        <span class="ui-icon ui-icon-tag"></span>
    {/if}
    {if $mail->getCustomer()}
        <span class="ui-icon ui-icon-person"></span>
    {/if}
{/block}