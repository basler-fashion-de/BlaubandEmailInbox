<?php

use BlaubandEmail\BlaubandEmail;
use BlaubandEmail\Models\LoggedMail;
use BlaubandEmailInbox\Controllers\Backend\BlaubandController;
use BlaubandEmailInbox\Services\SearchService;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Model\ModelManager;

class Shopware_Controllers_Backend_BlaubandOverview extends BlaubandController implements CSRFWhitelistAware
{
    public function getWhitelistedCSRFActions()
    {
        return [
            'index',
        ];
    }

    public function preDispatch()
    {
        $this->view->addTemplateDir(
            $this->container->getParameter('blauband_email.plugin_dir') . "/Resources/views"
        );

        $this->view->addTemplateDir(
            $this->container->getParameter('blauband_email_inbox.plugin_dir') . "/Resources/views"
        );
    }

    public function indexAction()
    {
        $shopwareVersion = Shopware()->Config()->get( 'Version' );
        $showSystemMailFilter = version_compare($shopwareVersion, '5.6.0', '>=');

        /** @var SearchService $searchService */
        $searchService = $this->get('blauband_email_inbox.services.search_service');

        $limit = $this->request->getParam('limit', BlaubandEmail::PAGE_LIMIT);
        $offset = $this->request->getParam('offset', 0);

        $relationshipSelect = $this->request->getParam('relationshipSelect', SearchService::RELATIONSHIP_STATE_ALL);
        $isSystemMail = $this->request->getParam('showSystemMail') === 'true';

        $searchDone = false;
        $searchProgress = $isInProgress = $this->request->getParam('stateInProgress') === 'true';
        $searchTodo = $isTodo = $this->request->getParam('stateTodo') === 'true';

        //Wenn nichts ausgewählt, dann alle
        if(!$isInProgress && !$isTodo){
            $searchProgress = $searchTodo = $searchDone = true;
        }

        list($mails, $total) = $searchService->searchLoggedMail(
            $limit,
            $offset,
            $relationshipSelect,
            $searchDone,
            $searchProgress,
            $searchTodo,
            $isSystemMail || !$showSystemMailFilter
        );

        $this->view->assign('mails', $mails);
        $this->view->assign('offset', $offset);
        $this->view->assign('limit', $limit);
        $this->view->assign('total', $total);

        $this->view->assign('relationshipSelect', $relationshipSelect);
        $this->view->assign('stateInProgress', $isInProgress);
        $this->view->assign('stateTodo', $isTodo);
        $this->view->assign('showSystemMail', $isSystemMail);

        $this->view->assign('showSystemMailFilter', $showSystemMailFilter);
    }
}