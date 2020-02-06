<?php

namespace BlaubandEmailInbox\Services;

use BlaubandEmail\Models\LoggedMail;
use Doctrine\DBAL\Driver\Connection;
use Shopware\Components\Model\ModelManager;

class SearchService
{
    const LIMIT = 10;

    const RELATIONSHIP_STATE_BOTH = 'both';
    const RELATIONSHIP_STATE_CUSTOMER = 'customer';
    const RELATIONSHIP_STATE_ORDER = 'order';
    const RELATIONSHIP_STATE_NONE = 'none';
    const RELATIONSHIP_STATE_ALL = 'all';
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct(ModelManager $modelManager, Connection $connection)
    {
        $this->modelManager = $modelManager;
        $this->connection = $connection;
    }

    public function searchLoggedMail($limit, $offset, $relationshipSelect, $isDone, $isInProgress, $isTodo, $isSystemMail)
    {
        $repository = $this->modelManager->getRepository(LoggedMail::class);
        $queryBuilder = $repository->createQueryBuilder('mail');

        if ($relationshipSelect === self::RELATIONSHIP_STATE_BOTH || $relationshipSelect === self::RELATIONSHIP_STATE_ORDER) {
            $queryBuilder->andWhere($queryBuilder->expr()->isNotNull('mail.orderId'));
        }

        if ($relationshipSelect === self::RELATIONSHIP_STATE_BOTH || $relationshipSelect === self::RELATIONSHIP_STATE_CUSTOMER) {
            $queryBuilder->andWhere($queryBuilder->expr()->isNotNull('mail.customerId'));
        }

        if ($relationshipSelect === self::RELATIONSHIP_STATE_NONE) {
            $queryBuilder->andWhere($queryBuilder->expr()->isNull('mail.customerId'));
            $queryBuilder->andWhere($queryBuilder->expr()->isNull('mail.orderId'));
        }

        if (!$isDone) {
            $queryBuilder->andWhere($queryBuilder->expr()->neq('mail.state', ':done'));
            $queryBuilder->setParameter(':done', LoggedMail::STATE_DONE);
        }

        if (!$isInProgress) {
            $queryBuilder->andWhere($queryBuilder->expr()->neq('mail.state', ':inProgress'));
            $queryBuilder->setParameter(':inProgress', LoggedMail::STATE_IN_PROGRESS);
        }

        if (!$isTodo) {
            $queryBuilder->andWhere($queryBuilder->expr()->neq('mail.state', ':todo'));
            $queryBuilder->setParameter(':todo', LoggedMail::STATE_TODO);
        }

        if (!$isSystemMail) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq('mail.isSystemMail', ':isSystemMail'));
            $queryBuilder->setParameter(':isSystemMail', 0);
        }

        $queryBuilder->select('count(mail.id)');
        $total = $queryBuilder->getQuery()->getSingleScalarResult();

        $queryBuilder->select('mail');
        $queryBuilder->addOrderBy('mail.createDate', 'DESC');
        $queryBuilder->setFirstResult($offset)->setMaxResults($limit);
        $mails = $queryBuilder->getQuery()->getResult();

        return [$mails, $total];
    }

    public function searchCustomers($term, $page)
    {
        $searchQuery = $this->getSearchTermQuery($term);
        $customers = $this->connection->fetchAll("
                SELECT id, CONCAT(firstname, ' ', lastname, ' <', email, '>')  AS text
                FROM s_user 
                WHERE $searchQuery
                LIMIT :offset, :limit
                ",
            [
                'term' => '%' . $term . '%',
                'offset' => ($page - 1) * self::LIMIT,
                'limit' => self::LIMIT
            ], [
                'offset' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT
            ]);

        $total = $this->connection->fetchColumn("
                SELECT COUNT(*) FROM s_user WHERE $searchQuery",
            [
                'term' => '%' . $term . '%'
            ]);

        return [$customers, $total];
    }

    public function searchOrders($term, $page)
    {
        $searchQuery = $this->getSearchTermQuery($term);

        $orders = $this->connection->fetchAll("
                SELECT s_order.id, CONCAT(ordernumber, ' | ', firstname, ' ', lastname, ' <', email, '>')  AS text
                FROM s_order 
                JOIN s_user ON (s_order.userID = s_user.id)
                WHERE $searchQuery OR ordernumber like :term
                LIMIT :offset, :limit
                ",
            [
                'term' => '%' . $term . '%',
                'offset' => ($page - 1) * self::LIMIT,
                'limit' => self::LIMIT
            ], [
                'offset' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT
            ]);

        $total = $this->connection->fetchColumn("
                SELECT COUNT(*) FROM s_order 
                JOIN s_user ON (s_order.userID = s_user.id)
                WHERE $searchQuery OR ordernumber like :term",
            [
                'term' => '%' . $term . '%'
            ]);

        return [$orders, $total];

    }

    private function getSearchTermQuery($term)
    {
        if (empty($term)) {
            return ' 1=1 ';
        }

        return ' firstname LIKE :term 
            OR lastname LIKE :term
            OR email LIKE :term ';
    }
}