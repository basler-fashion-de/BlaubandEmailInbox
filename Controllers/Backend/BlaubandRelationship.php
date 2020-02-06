<?php

use BlaubandEmailInbox\Controllers\Backend\BlaubandController;
use BlaubandEmail\Models\LoggedMail;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Order\Order;

class Shopware_Controllers_Backend_BlaubandRelationship extends BlaubandController implements CSRFWhitelistAware
{
    const LIMIT = 10;

    public function getWhitelistedCSRFActions()
    {
        return [
            'setOrder',
            'setCustomer'
        ];
    }

    public function setOrderAction()
    {
        try {
            $orderId = $this->request->getParam('valueId');
            $mailId = $this->request->getParam('mailId');

            $relationshipService = $this->get('blauband_email_inbox.services.relationship_service');

            $this->sendJson(['data' => $relationshipService->setOrder($mailId, $orderId)]);
        } catch (Exception $e) {
            $this->sendJson(['message' => $e->getMessage()], false);
        }
    }

    public function setCustomerAction()
    {
        try {
            $customerId = $this->Request()->getParam('valueId');
            $mailId = $this->Request()->getParam('mailId');

            $relationshipService = $this->get('blauband_email_inbox.services.relationship_service');

            $this->sendJson(['data' => $relationshipService->setCustomer($mailId, $customerId)]);
        } catch (Exception $e) {
            $this->sendJson(['message' => $e->getMessage()], false);
        }
    }
}