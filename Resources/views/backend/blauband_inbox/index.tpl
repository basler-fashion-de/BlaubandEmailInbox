{extends file="parent:backend/blauband_email/index.tpl"}

{block name="header"}
    {$smarty.block.parent}
    <link rel="stylesheet" href="{link file="backend/_public/src/css/email-inbox.css"}">
    <script type="text/javascript" src="{link file="backend/_public/src/js/email-inbox-common.js"}"></script>
    <script type="text/javascript" src="{link file="backend/_public/src/js/email-inbox-conf.js"}"></script>
    <script type="text/javascript" src="{link file="backend/_public/src/js/email-inbox-events.js"}"></script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.12/js/select2.full.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.12/css/select2.min.css">

    <script type="application/javascript">
      var successSaveMessage = '{s namespace="blauband/mail" name="connectionSavedSuccessful"}{/s}';
      var successDeleteMessage = '{s namespace="blauband/mail" name="connectionDeleteSuccessful"}{/s}';
      var searchMessage = '{s namespace="blauband/mail" name="search"}{/s}';
      var mailHeader = [];
    </script>
{/block}

{block name="main-content"}
    <div id="blauband-mail-inbox">
        {if $inboxConnections}
            {include file="backend/blauband_inbox/alert.tpl"}

            <h2>
                {s namespace="blauband/mail" name="inboxConnectionsHeader"}{/s}
            </h2>
            <button id="connection-button" class="blue"
                    data-url="{url action="connection"}">
                {s namespace="blauband/mail" name="newConnection"}{/s}
            </button>

            <div class="cta-row">
                <div class="two-cols">
                    <label for="connectionSelect">{s namespace="blauband/mail" name="connectionName"}{/s}</label>
                    <select id="connectionSelect" name="connectionSelect">
                        {foreach $inboxConnections as $connection}
                            <option
                                    value="{$connection.id}"
                                    data-url="{url action="index" id={$connection.id}}"
                                    {if $connection.id == $id}selected{/if}>
                                {$connection.name}
                            </option>
                        {/foreach}
                    </select>
                </div>
                <div class="two-cols">
                    <label>&nbsp;</label>
                    <button id="edit-button" class="blue"
                            data-ajax-url="{url action="connection"}">
                        {s namespace="blauband/mail" name="editConnection"}{/s}
                    </button>
                    <button id="delete-button" class="blue"
                            data-ajax-url="{url action="crud"}">
                        {s namespace="blauband/mail" name="deleteConnection"}{/s}
                    </button>
                </div>
            </div>
        {else}
            <div class="center">
                {s namespace="blauband/mail" name="noConnections"}{/s}
                <button id="connection-button" class="blue"
                        data-url="{url action="connection"}">
                    {s namespace="blauband/mail" name="newConnection"}{/s}
                </button>
            </div>
        {/if}
    </div>
{/block}