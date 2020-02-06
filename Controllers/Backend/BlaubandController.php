<?php

namespace BlaubandEmailInbox\Controllers\Backend;

class BlaubandController extends \Enlight_Controller_Action
{
    /**
     * @param $data
     * @param bool $succes
     * @throws \Exception
     */
    protected function sendJson($data, $succes = true)
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Response()->setBody(json_encode(['success' => $succes, 'data' => $data]));
        $this->Response()->setHeader('Content-type', 'application/json', true);
    }

}