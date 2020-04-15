<?php

namespace BlaubandEmailInbox\Services;

use BlaubandEmail\Models\LoggedMail;
use Shopware\Components\Model\ModelManager;

class StateService
{
    /**
     * @var ModelManager
     */
    private $modelManager;
    /**
     * @var \Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    public function __construct(
        ModelManager $modelManager,
        \Shopware_Components_Snippet_Manager $snippetManager
    ) {
        $this->modelManager   = $modelManager;
        $this->snippetManager = $snippetManager;
    }

    public function setState($mailId, $state)
    {
        if (empty($mailId)) {
            throw new \RuntimeException($this->snippetManager->getNamespace('blauband/mail')->get('missingId'));
        }

        /** @var LoggedMail $mail */
        $mail = $this->modelManager->find(LoggedMail::class, $mailId);

        if ($mail === null) {
            throw new \RuntimeException($this->snippetManager->getNamespace('blauband/mail')->get('missingMail'));
        }

        $mail->setState($state);

        $this->modelManager->flush($mail);

        return true;
    }
}