<?php

namespace BlaubandEmailInbox\Services;

use BlaubandEmail\Models\LoggedMail;
use BlaubandEmailInbox\Components\InboxComponent;
use BlaubandEmailInbox\Models\InboxConnection;
use BlaubandEmailInbox\Models\InboxConnectionRepository;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Order\Order;

class InboxService
{

    /**
     * @var InboxConnectionRepository
     */
    private $repository;
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * InboxService constructor.
     *
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
        $this->repository   = $modelManager->getRepository(InboxConnection::class);

    }

    /**
     * @return array
     */
    public function getInboxConnections($asArray = false)
    {
        if($asArray){
            return $this->modelManager->toArray($this->repository->findAll());
        }

        return $this->repository->findAll();
    }

    /**
     * @param $id
     * @param bool $asArray
     *
     * @return InboxConnection | array
     * @throws \Exception
     */
    public function getInboxConnection($id, $asArray = false)
    {
        if (!$id) {
            throw new \Exception('Connection id is empty');
        }

        /** @var InboxConnection $connection */
        $connection = $this->repository->find($id);

        if (!$connection) {
            throw new \Exception("No connection settings found by id '$id'");
        }

        if ($asArray) {
            return $this->modelManager->toArray($connection);
        }

        return $connection;
    }

    /**
     * @param InboxConnection $connection
     *
     * @return InboxComponent
     * @throws \Exception
     */
    public function getComponentByConnection(InboxConnection $connection)
    {
        if (!$connection) {
            throw new \Exception("No connection");
        }

        $component = new InboxComponent(
            $connection->getUsername(),
            $connection->getPassword(),
            $connection->getType(),
            $connection->getHost(),
            $connection->getPort(),
            $connection->getFolder(),
            $connection->isSsl()
        );

        if (!$component->isConnected()) {
            throw new \Exception(
                "Could not connect to mailbox '" . $connection->getName() . "' " . imap_last_error()
            );
        }

        return $component;
    }

    /**
     * @param $id
     *
     * @return InboxComponent
     * @throws \Exception
     */
    public function getComponentByConnectionId($id)
    {
        $connection = $this->getInboxConnection($id);

        return $this->getComponentByConnection($connection);
    }

    /**
     * @param $name
     * @param $type
     * @param $username
     * @param $password
     * @param $host
     * @param $port
     * @param $folder
     * @param $ssl
     *
     * @return array
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createConnection($name, $type, $username, $password, $host, $port, $folder, $ssl)
    {
        $connection = new InboxConnection();
        $connection->setName($name);
        $connection->setType($type);
        $connection->setUsername($username);
        $connection->setPassword($password);
        $connection->setHost($host);
        $connection->setPort($port);
        $connection->setFolder($folder);
        $connection->setSsl($ssl);

        $this->modelManager->persist($connection);
        $this->modelManager->flush($connection);

        return $this->modelManager->toArray($connection);
    }

    /**
     * @param $id
     * @param $name
     * @param $username
     * @param $password
     * @param $host
     * @param $port
     * @param $folder
     * @param $ssl
     *
     * @return array
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateConnection($id, $name, $type, $username, $password, $host, $port, $folder, $ssl)
    {
        /** @var InboxConnection $connection */
        $connection = $this->repository->find($id);

        $connection->setName($name);
        $connection->setType($type);
        $connection->setUsername($username);
        $connection->setPassword($password);
        $connection->setHost($host);
        $connection->setPort($port);
        $connection->setFolder($folder);
        $connection->setSsl($ssl);

        $this->modelManager->flush($connection);

        return $this->modelManager->toArray($connection);
    }

    /**
     * @param $id
     *
     * @return bool
     * @throws \Doctrine\ORM\OptimisticLockException | \Exception
     */
    public function deleteInboxConnection($id)
    {
        $connection = $this->getInboxConnection($id);
        $this->modelManager->remove($connection);
        $this->modelManager->flush($connection);

        return true;
    }

    public function getLoggedMail($header, $body, $attachment)
    {
        $mail = new LoggedMail();
        $mail->setSubject($header->subject);
        $mail->setFrom($header->from);
        $mail->setTo($header->to);
        $mail->setCreateDate(date('Y-m-d H:i:s', strtotime($header->date)));
        $mail->setBody($body);
        $mail->setIsHtml(true);
        $mail->setIsSystemMail(false);

        if ($attachment) {
            $mail->setAttachments(json_encode($attachment));
        }

        $mail->setCustomer($this->getCustomerByEmail($header->from));
        $mail->setOrder($this->getOrderByContent($header->from, $header->subject . ' ' . $body));

        $existsMail = $this->getExistingMail($mail);

        if ($existsMail === false) {
            $this->modelManager->persist($mail);
            $this->modelManager->flush($mail);

            return $mail;
        }

        return $existsMail;
    }

    /**
     * @param $mail
     *
     * @return Customer|null
     */
    private function getCustomerByEmail($mail)
    {
        preg_match('/.*<(.*)>/m', $mail, $matches);
        $repository = $this->modelManager->getRepository(Customer::class);

        return $repository->findOneBy(['email' => $matches[1]]);
    }

    private function getOrderByContent($mail, $content)
    {
        $costumer = $this->getCustomerByEmail($mail);

        if ($costumer) {
            $orders = $costumer->getOrders();

            /** @var Order $order */
            foreach ($orders as $order) {
                if (strpos($content, $order->getNumber()) !== false) {
                    return $order;
                }
            }
        }

        return null;
    }

    private function getExistingMail(LoggedMail $mail)
    {
        $loggedMailRepository = $this->modelManager->getRepository(LoggedMail::class);
        $loggedMail           = $loggedMailRepository->findOneBy(
            [
                'from'       => $mail->getFrom(),
                'to'         => $mail->getTo(),
                'subject'    => $mail->getSubject(),
                'createDate' => $mail->getCreateDate(),
            ]
        );

        if (!$loggedMail) {
            return false;
        }

        return $loggedMail;
    }
}