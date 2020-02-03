{extends file="parent:backend/blauband_inbox/index.tpl"}

{block name="main-content"}
    <div id="blauband-mail-inbox">
        {include file="backend/blauband_inbox/alert.tpl"}

        <h2>
            {s namespace="blauband/mail" name="connectionHeader"}{/s}
        </h2>
        <hr/>

        <div class="button-right-wrapper">
            <button id="back-button" data-url="{url action="index"}">
                {s namespace="blauband/mail" name="back"}{/s}
            </button>

            <button id="save-button" class="blue" data-url="{url action="crud"}">
                {s namespace="blauband/mail" name="saveConnection"}{/s}
            </button>
        </div>

        <div style="clear: both"></div>

        <input type="hidden" id="connectionId" name="connectionId" value="{$connection.id}"/>
        <div class="cta-row">
            <div class="two-cols">
                <label for="connectionName">{s namespace="blauband/mail" name="connectionName"}{/s}</label>
                <input type="text" id="connectionName" name="connectionName" value="{$connection.name}"/>
            </div>
            <div class="two-cols">
                <label for="connectionType">{s namespace="blauband/mail" name="type"}{/s}</label>
                <select id="connectionType" name="connectionType">
                    <option value="imap">{s namespace="blauband/mail" name="imap"}{/s}</option>
                    <option value="pop3">{s namespace="blauband/mail" name="pop3"}{/s}</option>
                </select>
            </div>
        </div>
        <div class="cta-row">
            <div class="two-cols">
                <label for="connectionHost">{s namespace="blauband/mail" name="host"}{/s}</label>
                <input type="text" id="connectionHost" name="connectionHost" value="{$connection.host}"/>
            </div>
            <div class="two-cols">
                <label for="connectionPort">{s namespace="blauband/mail" name="port"}{/s}</label>
                <input type="text" id="connectionPort" name="connectionPort" value="{if $connection.port}{$connection.port}{else}110{/if}"/>
            </div>
        </div>
        <div class="cta-row">
            <div class="two-cols">
                <label for="connectionUsername">{s namespace="blauband/mail" name="username"}{/s}</label>
                <input type="text" id="connectionUsername" name="connectionUsername" value="{$connection.username}"/>
            </div>
            <div class="two-cols">
                <label for="connectionPassword">{s namespace="blauband/mail" name="password"}{/s}</label>
                <input type="text" id="connectionPassword" name="connectionPassword" value="{$connection.password}"/>
            </div>
        </div>
        <div class="cta-row">
            <div class="two-cols">
                <label for="connectionFolder">{s namespace="blauband/mail" name="folder"}{/s}</label>
                <input type="text" id="connectionFolder" name="connectionFolder" value="{if $connection.folder}{$connection.folder}{else}INBOX{/if}"/>
            </div>
            <div class="two-cols">
                <label for="connectionSsl">{s namespace="blauband/mail" name="ssl"}{/s}</label>
                <input type="checkbox" id="connectionSsl" name="connectionSsl" {if $connection.ssl}checked="checked"{/if}"/>
            </div>
        </div>
    </div>
{/block}