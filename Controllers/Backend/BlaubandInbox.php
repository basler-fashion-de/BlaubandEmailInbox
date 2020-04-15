<?php

use BlaubandEmailInbox\Controllers\Backend\BlaubandController;
use BlaubandEmail\BlaubandEmail;
use BlaubandEmailInbox\Models\InboxConnection;
use BlaubandEmailInbox\Services\InboxService;
use Shopware\Components\CSRFWhitelistAware;

class Shopware_Controllers_Backend_BlaubandInbox extends BlaubandController implements CSRFWhitelistAware
{
    public function getWhitelistedCSRFActions()
    {
        return [
            'index',
            'connection',
            'crud',
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
        /** @var InboxService $inboxService */
        $inboxService = $this->get('blauband_email_inbox.services.inbox_service');
        /** @var InboxConnection[] $inboxConnections */
        $inboxConnections = $inboxService->getInboxConnections(true);

        $this->view->assign('inboxConnections', $inboxConnections);

        $id = $this->request->getParam('id');

        $limit = $this->request->getParam('limit', BlaubandEmail::PAGE_LIMIT);
        $offset = $this->request->getParam('offset', 0);

        if (!$id && $inboxConnections) {
            $id = $inboxConnections[0]['id'];
        }

        if($id){
            try{
                $component = $inboxService->getComponentByConnectionId($id);
                $headers = $component->getSortedHeaders($offset/$limit+1, $limit);
                $mails = [];

                foreach ($headers['res'] as $header){
                    $parts = $component->getParts($header);
                    $mails[] = $inboxService->getLoggedMail($header, $parts['text'], $parts['attachment']);
                }

                $this->view->assign('id', $id);
                $this->view->assign('mails', $mails);
                $this->view->assign('offset', $offset);
                $this->view->assign('limit', $limit);
                $this->view->assign('total', $headers['total']);
            }catch (Exception $e){}
        }
    }

    /**
     * @throws Exception
     */
    public function connectionAction()
    {
        /** @var \BlaubandEmailInbox\Services\InboxService $inboxService */
        $inboxService = $this->get('blauband_email_inbox.services.inbox_service');

        if ($this->request->getParam('id')) {
            $connection = $inboxService->getInboxConnection($this->request->getParam('id'), true);
            $this->view->assign('connection', $connection);
        }
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function crudAction()
    {
        /** @var \BlaubandEmailInbox\Services\InboxService $inboxService */
        $inboxService = $this->get('blauband_email_inbox.services.inbox_service');

        if ($this->request->getParam('deleteId')) {
            $inboxService->deleteInboxConnection($this->request->getParam('deleteId'));
            $this->sendJson([]);
            return;
        }

        $error = $this->validateRequestParams($this->request->getParams());
        if ($error) {
            $this->sendJson(['message' => $error], false);
            return;
        }

        if ($this->request->getParam('connectionId')) {
            $connection = $inboxService->updateConnection(
                $this->request->getParam('connectionId'),
                $this->request->getParam('connectionName'),
                $this->request->getParam('connectionType'),
                $this->request->getParam('connectionUsername'),
                $this->request->getParam('connectionPassword'),
                $this->request->getParam('connectionHost'),
                $this->request->getParam('connectionPort'),
                $this->request->getParam('connectionFolder'),
                $this->request->getParam('connectionSsl') === 'true'
            );
        } else {
            $connection = $inboxService->createConnection(
                $this->request->getParam('connectionName'),
                $this->request->getParam('connectionType'),
                $this->request->getParam('connectionUsername'),
                $this->request->getParam('connectionPassword'),
                $this->request->getParam('connectionHost'),
                $this->request->getParam('connectionPort'),
                $this->request->getParam('connectionFolder'),
                $this->request->getParam('connectionSsl') === 'true'
            );
        }

        $this->sendJson($connection);
    }

    /**
     * @param $params
     * @return mixed
     * @throws Exception
     */
    private function validateRequestParams($params)
    {
        if (empty($params['connectionName']) ||
            empty($params['connectionType']) ||
            empty($params['connectionUsername']) ||
            empty($params['connectionPassword']) ||
            empty($params['connectionHost']) ||
            empty($params['connectionPort']) ||
            empty($params['connectionFolder'])
        ) {
            return $this->get('snippets')->getNamespace('blauband/mail')->get('missingParameter');
        }
    }
}