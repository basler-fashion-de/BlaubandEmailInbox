{extends file="backend/blauband_email/mails.tpl"}

{block name="mail-list--header--link"}
    <div
            class="mail--list-item--state--{$mail->getState()}"
            data-mail-id="{$mail->getId()}"
            data-mail-url="{url controller="BlaubandStates" action="setDone" id={$mail->getId()}}"
    >
        {$smarty.block.parent}
    </div>
{/block}

{block name="mail-list--header--content"}
    {$smarty.block.parent}

    {if $mail->getState()}
        {if $mail->getState() === {"BlaubandEmail\Models\LoggedMail::STATE_DONE"|constant} }
            <span class="state-icon ui-icon ui-icon-check"></span>
        {/if}

        {if $mail->getState() === {"BlaubandEmail\Models\LoggedMail::STATE_IN_PROGRESS"|constant}}
            <span class="state-icon ui-icon ui-icon-flag"></span>
        {/if}

        {if $mail->getState() === {"BlaubandEmail\Models\LoggedMail::STATE_TODO"|constant}}
            <span class="state-icon ui-icon ui-icon-notice"></span>
        {/if}
    {/if}

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
            {s namespace="blauband/mail" name="assignOrder"}{/s}
            <br/>
            <select class='select2 select2--order no-ui'
                    data-url="{url controller="BlaubandSearch" action="searchOrder"}"
                    data-save-url="{url controller="BlaubandRelationship" action="setOrder"}"
                    data-mail-id="{$mail->getId()}"
            ></select>
        {/if}

        {if empty($mail->getOrder()) && empty($mail->getCustomer())}
            <br/>
        {/if}

        {if empty($mail->getCustomer())}
            {s namespace="blauband/mail" name="assignCustomer"}{/s}
            <br/>
            <select class='select2 select2--customer no-ui'
                    data-url="{url controller="BlaubandSearch" action="searchCustomer"}"
                    data-save-url="{url controller="BlaubandRelationship" action="setCustomer"}"
                    data-mail-id="{$mail->getId()}"
            ></select>
        {/if}
    {/if}
{/block}

{block name="mail-list--body--attribute--date"}
    <div class="title-date">
        {$createDate}<br/>

        {if $mail->getState()}
            {if
            $mail->getState() === {"BlaubandEmail\Models\LoggedMail::STATE_DONE"|constant}  ||
            $mail->getState() === {"BlaubandEmail\Models\LoggedMail::STATE_TODO"|constant}
            }
                <button type="button"
                        class="change-state--button"
                        data-state-change-url="{url controller="BlaubandStates" action="setInProgress" id={$mail->getId()}}">
                    <span class="state-icon ui-icon ui-icon-flag"></span>
                </button>
            {/if}

            {if $mail->getState() === {"BlaubandEmail\Models\LoggedMail::STATE_IN_PROGRESS"|constant}}
                <button type="button"
                        class="change-state--button"
                        data-state-change-url="{url controller="BlaubandStates" action="setDone" id={$mail->getId()}}">
                    <span class="state-icon ui-icon ui-icon-flag red-icon"></span>
                </button>
            {/if}
        {/if}

        {if !empty($mail->getCustomer())}
            {assign customerId $mail->getCustomer()->getId()}
        {/if}

        {if !empty($mail->getOrder())}
            {assign orderId $mail->getOrder()->getId()}
        {/if}

        <button
                type="button"
                class="reply-mail--button"
                data-url="{url controller="BlaubandEmail" action="send" customerId=$customerId orderId=$orderId mailId=$mail->getId()}">
            <span class="ui-icon ui-icon-arrowreturnthick-1-e"></span>
        </button>

    </div>
{/block}

{block name="mail-list--body--attribute--customer-link"}
    {$smarty.block.parent}
    <button
            type="button"
            class="delete-relationship"
            data-ajax-url="{url controller="BlaubandRelationship" action="setCustomer"}"
            data-mail-id="{$mail->getId()}">
        <span class="ui-icon ui-icon-closethick"></span>
    </button>
{/block}

{block name="mail-list--body--attribute--order-link"}
    {$smarty.block.parent}
    <button
            type="button"
            class="delete-relationship"
            data-ajax-url="{url controller="BlaubandRelationship" action="setOrder"}"
            data-mail-id="{$mail->getId()}">
        <span class="ui-icon ui-icon-closethick"></span>
    </button>
{/block}