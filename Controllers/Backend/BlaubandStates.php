<?php

use BlaubandEmail\Models\LoggedMail;
use BlaubandEmailInbox\Controllers\Backend\BlaubandController;
use Shopware\Components\CSRFWhitelistAware;

class Shopware_Controllers_Backend_BlaubandStates extends BlaubandController implements CSRFWhitelistAware
{
    public function getWhitelistedCSRFActions()
    {
        return [
            'setDone',
            'setInProgress'
        ];
    }

    public function setDoneAction()
    {
        try {
            $id = $this->request->getParam('id');

            $stateService = $this->get('blauband_email_inbox.services.state_service');

            $this->sendJson(['data' => $stateService->setState($id, LoggedMail::STATE_DONE)]);
        } catch (Exception $e) {
            $this->sendJson(['message' => $e->getMessage()], false);
        }
    }

    public function setInProgressAction()
    {
        try {
            $id = $this->request->getParam('id');

            $stateService = $this->get('blauband_email_inbox.services.state_service');

            $this->sendJson(['data' => $stateService->setState($id, LoggedMail::STATE_IN_PROGRESS)]);
        } catch (Exception $e) {
            $this->sendJson(['message' => $e->getMessage()], false);
        }
    }
}