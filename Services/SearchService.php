<?php

namespace BlaubandEmailInbox\Services;

use Doctrine\DBAL\Driver\Connection;

class SearchService
{
    const LIMIT = 10;
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function searchCustomers($term, $page){
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

    public function searchOrders($term, $page){
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