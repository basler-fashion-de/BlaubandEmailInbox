<?php

namespace BlaubandEmailInbox\Services;

use BlaubandEmail\Models\LoggedMail;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Order\Order;

class RelationshipService
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

    public function setOrder($mailId, $orderId = null)
    {
        if (empty($mailId)) {
            throw new \RuntimeException($this->snippetManager->getNamespace('blauband/mail')->get('missingId'));
        }

        /** @var LoggedMail $mail */
        $mail = $this->modelManager->find(LoggedMail::class, $mailId);

        if ($mail === null) {
            throw new \RuntimeException($this->snippetManager->getNamespace('blauband/mail')->get('missingMail'));
        }

        if($orderId === null){
            $order = null;
        }else{
            /** @var Order $order */
            $order = $this->modelManager->find(Order::class, $orderId);

            if ($order === null) {
                throw new \RuntimeException($this->snippetManager->getNamespace('blauband/mail')->get('missingOrder'));
            }
        }

        $mail->setOrder($order);
        $this->modelManager->flush($mail);

        return $orderId;
    }

    public function setCustomer($mailId, $customerId = null)
    {
        if (empty($mailId)) {
            throw new \RuntimeException($this->snippetManager->getNamespace('blauband/mail')->get('missingId'));
        }

        /** @var LoggedMail $mail */
        $mail = $this->modelManager->find(LoggedMail::class, $mailId);

        if ($mail === null) {
            throw new \RuntimeException($this->snippetManager->getNamespace('blauband/mail')->get('missingMail'));
        }

        if($customerId === null){
            $customer = null;
        }else{
            /** @var Customer $customer */
            $customer = $this->modelManager->find(Customer::class, $customerId);

            if ($customer === null) {
                throw new \RuntimeException($this->snippetManager->getNamespace('blauband/mail')->get('missingCustomer'));
            }
        }

        $mail->setCustomer($customer);
        $this->modelManager->flush($mail);

        return $customerId;
    }
}