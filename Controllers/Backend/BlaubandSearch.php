<?php

use Shopware\Components\CSRFWhitelistAware;
use BlaubandEmailInbox\Services\SearchService;

class Shopware_Controllers_Backend_BlaubandSearch extends \Enlight_Controller_Action implements CSRFWhitelistAware
{
    public function getWhitelistedCSRFActions()
    {
        return [
            'searchCustomer',
            'searchOrder'
        ];
    }

    public function searchOrderAction(){
        $term = $this->request->getParam('term', null);
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

    /**
     * @param $data
     * @param bool $succes
     * @throws Exception
     */
    private function sendJson($data, $succes = true)
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Response()->setBody(json_encode(['success' => $succes, 'data' => $data]));
        $this->Response()->setHeader('Content-type', 'application/json', true);
    }
}