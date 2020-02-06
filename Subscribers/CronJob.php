<?php

namespace BlaubandEmailInbox\Subscribers;

use BlaubandEmailInbox\Services\InboxService;
use Enlight\Event\SubscriberInterface;

class CronJob implements SubscriberInterface
{
    /**
     * @var InboxService
     */
    private $inboxService;

    public function __construct(
        InboxService $inboxService
    ) {
        $this->inboxService = $inboxService;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Shopware_CronJob_BlaubandInboxCronJob' => 'onCronJob',
        ];
    }

    public function onCronJob(\Shopware_Components_Cron_CronJob $job)
    {
        try {
            $connections = $this->inboxService->getInboxConnections();

            foreach ($connections as $connection) {
                $component = $this->inboxService->getComponentByConnection($connection);
                $headers   = $component->getSortedHeadersByToday();

                foreach ($headers['res'] as $header) {
                    $parts = $component->getParts($header);
                    $this->inboxService->getLoggedMail($header, $parts['text'], $parts['attachment']);
                }
            }
        } catch (\Exception $e) {
        }
    }
}