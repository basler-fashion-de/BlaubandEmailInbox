<?php

use BlaubandEmailInbox\Controllers\Backend\BlaubandController;
use Shopware\Components\CSRFWhitelistAware;
use BlaubandEmailInbox\Services\SearchService;

class Shopware_Controllers_Backend_BlaubandSearch extends BlaubandController implements CSRFWhitelistAware
{
    public function getWhitelistedCSRFActions()
    {
        return [
            'searchCustomer',
            'searchOrder'
        ];
    }

    public function searchOrderAction(){
        $term = $this->request->getParam('term');
        $page = $this->request->getParam('page', 1);

        list($orders, $total) = $this->get('blauband_email_inbox.services.search_service')->searchOrders($term, $page);

        $this->sendJson(['result' => $orders, 'more' => $total > $page * SearchService::LIMIT]);
    }

    public function searchCustomerAction()
    {
        $term = $this->request->getParam('term', null);
        $page = $this->request->getParam('page', 1);

        list($customers, $total) = $this->get('blauband_email_inbox.services.search_service')->searchCustomers($term, $page);

        $this->sendJson(['result' => $customers, 'more' => $total > $page * SearchService::LIMIT]);
    }
}