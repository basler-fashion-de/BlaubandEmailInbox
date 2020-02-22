<?php

namespace BlaubandEmailInbox\Services;

use Doctrine\DBAL\Driver\Connection;

class EmailService
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection, InboxService $inboxService)
    {
        $this->connection = $connection;
        $this->inboxService = $inboxService;
    }

}