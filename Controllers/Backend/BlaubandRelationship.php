<?php

use Shopware\Components\CSRFWhitelistAware;

class Shopware_Controllers_Backend_BlaubandRelationship extends \Enlight_Controller_Action implements CSRFWhitelistAware
{
    const LIMIT = 10;

    public function getWhitelistedCSRFActions()
    {
        return [
            'setRelationship'
        ];
    }

    public function setOrder(){

    }

    public function setCustomer(){
die buttons zum speichern fertig machen
    }
}