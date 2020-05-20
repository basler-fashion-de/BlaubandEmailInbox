<div class="cta-row">
    <div class="two-cols">
        <label for="relationship">{s namespace="blauband/mail" name="relationship"}{/s}</label>
        <select id="relationshipSelect" name="relationshipSelect" class="filter-input">
            <option {if $relationshipSelect == 'all'}selected
                    {/if}value="all">{s namespace="blauband/mail" name="allRelationship"}{/s}</option>
            <option {if $relationshipSelect == 'customer'}selected
                    {/if}value="customer">{s namespace="blauband/mail" name="customerRelationship"}{/s}</option>
            <option {if $relationshipSelect == 'order'}selected
                    {/if}value="order">{s namespace="blauband/mail" name="orderRelationship"}{/s}</option>
            <option {if $relationshipSelect == 'none'}selected
                    {/if}value="none">{s namespace="blauband/mail" name="noneRelationship"}{/s}</option>
        </select>
    </div>
    <div class="two-cols">
        <label>{s namespace="blauband/mail" name="state"}{/s}</label>

        <div class="checkbox-wrapper">
            <input class="filter-input" type="checkbox" id="stateTodo" name="stateTodo"{if $stateTodo} checked{/if}>
            <label class="checkbox" for="stateTodo">{s namespace="blauband/mail" name="todo"}{/s}</label>

            <input class="filter-input" type="checkbox" id="stateInProgress"
                   name="stateInProgress"{if $stateInProgress} checked{/if}>
            <label class="checkbox" for="stateInProgress">{s namespace="blauband/mail" name="in_progress"}{/s}</label>
        </div>
    </div>
</div>

{if $showSystemMailFilter}
    <div class="cta-row">
        <div class="two-cols">

        </div>
        <div class="two-cols">
            <div class="checkbox-wrapper">
                <input class="filter-input" type="checkbox" id="showSystemMail"
                       name="showSystemMail"{if $showSystemMail} checked{/if}>
                <label class="checkbox"
                       for="showSystemMail">{s namespace="blauband/mail" name="showSystemMail"}{/s}</label>
            </div>
        </div>
    </div>
{/if}