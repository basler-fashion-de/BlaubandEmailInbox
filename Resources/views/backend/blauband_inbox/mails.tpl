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

{block name="mail-list--body--attribute"}
    {$smarty.block.parent}

    {if empty($mail->getOrder()) || empty($mail->getCustomer())}
        <br/>
        {if empty($mail->getOrder())}
            {s namespace="blauband/mail" name="assignOrder"}{/s}<br/>
            <select class='select2 select2--order no-ui'
                    data-url="{url controller="BlaubandSearch" action="searchOrder"}"></select>
            <button
                    type="button"
                    class="save-relationship"
                    data-url="{url controller="BlaubandRelationship" action="setOrder"}"
                    data-mail-id="{$mail->getId()}"
                    data-related-select="select2--order">
                <span class="ui-icon ui-icon-disk"></span>
            </button>
        {/if}

        {if empty($mail->getOrder()) && empty($mail->getCustomer())}
            <br/>
        {/if}

        {if empty($mail->getCustomer())}
            {s namespace="blauband/mail" name="assignCustomer"}{/s}<br/>
            <select class='select2 select2--customer no-ui'
                    data-url="{url controller="BlaubandSearch" action="searchCustomer"}"></select>
            <button
                    type="button"
                    class="save-relationship"
                    data-url="{url controller="BlaubandRelationship" action="setCustomer"}"
                    data-mail-id="{$mail->getId()}"
                    data-related-select="select2--customer">
                <span class="ui-icon ui-icon-disk"></span>
            </button>
        {/if}
    {/if}
{/block}