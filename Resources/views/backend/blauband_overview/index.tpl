{extends file="parent:backend/blauband_email/index.tpl"}

{block name="header"}
    {$smarty.block.parent}
    <link rel="stylesheet" href="{link file="backend/_public/src/css/email-inbox.css"}">
    <script type="text/javascript" src="{link file="backend/_public/src/js/email-inbox-common.js"}"></script>
    <script type="text/javascript" src="{link file="backend/_public/src/js/email-inbox-conf.js"}"></script>
    <script type="text/javascript" src="{link file="backend/_public/src/js/email-inbox-events.js"}"></script>

    <script src="https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js"></script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.mjs"></script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.12/js/select2.full.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.12/css/select2.min.css">

    <script type="application/javascript">
        var baseUrl = '{url}/index/';
        var successSaveMessage = '{s namespace="blauband/mail" name="connectionSavedSuccessful"}{/s}';
        var successDeleteMessage = '{s namespace="blauband/mail" name="connectionDeleteSuccessful"}{/s}';
        var searchMessage = '{s namespace="blauband/mail" name="search"}{/s}';
        var mailHeader = [];
    </script>
{/block}

{block name="main-content"}
    <div id="blauband-mail-inbox">
        <h2>
            {s namespace="blauband/mail" name="inboxOverviewHeader"}{/s}
        </h2>

        {include file="backend/blauband_overview/filter.tpl"}

        <div class="mail-list" id="mails">
            {include file="backend/blauband_email/paging.tpl"}
            {include file="backend/blauband_overview/mails.tpl"}
        </div>
    </div>
{/block}