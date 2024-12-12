<?php

namespace Wrk;

use HTTP\HTTPResponses;
use PDO;
use PDOException;

/**
 * Class WrkDatabase
 * @package Wrk
 * @author Noé Henchoz
 * @date 2024-12
 */
class WrkDatabase {

    private static ?WrkDatabase $instance = null;
    private PDO $pdo;

    /**
     * WrkDatabase constructor. Try to connect to the database
     * @throws PDOException If an error occurs while connecting to the database
     */
    private function __construct() {
        try {
            $dbUrl = 'mysql:host=' . $_ENV["DB_HOST"] . ';port=' . $_ENV["DB_PORT"] . ';dbname=' . $_ENV["DB_NAME"];
            $this->pdo = new PDO($dbUrl, $_ENV["DB_USER"], $_ENV["DB_PASS"], [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                PDO::ATTR_PERSISTENT => true
            ]);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch ( PDOException $ex ) {
            HTTPResponses::error(500, $ex->getMessage());
        }
    }

    /**
     * Get the instance of the WrkDatabase class
     * @return WrkDatabase The instance of the WrkDatabase class
     */
    public static function getInstance(): WrkDatabase {
        if ( self::$instance === null ) {
            self::$instance = new WrkDatabase();
        }
        return self::$instance;
    }

    private function __clone() {
    }

    public function __wakeup() {
    }

    /**
     * Execute a SELECT query
     * @param string $query The query to execute
     * @param array $params The parameters to bind to the query
     * @param bool $fetchAll Whether to fetch all rows or not
     * @return array|bool The result of the query
     */
    public function select(string $query, array $params = [], bool $fetchAll = false): array|bool {
        try {
            $statement = $this->pdo->prepare($query);
            $statement->execute($params);
            return $fetchAll ? $statement->fetchAll(PDO::FETCH_ASSOC) : $statement->fetch(PDO::FETCH_ASSOC);
        } catch ( PDOException $ex ) {
            HTTPResponses::error(500, $ex->getMessage());
            // Never reached
            return false;
        }
    }

    /**
     * Execute an INSERT, UPDATE, or DELETE query
     * @param string $query The query to execute
     * @param array $params The parameters to bind to the query
     * @return bool Whether the query was successful or not
     */
    public function execute(string $query, array $params = []): bool {
        try {
            $statement = $this->pdo->prepare($query);
            return $statement->execute($params);
        } catch ( PDOException $ex ) {
            HTTPResponses::error(500, $ex->getMessage());
            // Never reached
            return false;
        }
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction(): void {
        $this->pdo->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit(): void {
        $this->pdo->commit();
    }

    /**
     * Roll back a transaction
     */
    public function rollBack(): void {
        $this->pdo->rollBack();
    }

    /**
     * Get the last inserted ID
     * @return bool|string The last inserted ID
     */
    public function lastInsertId(): bool|string {
        return $this->pdo->lastInsertId();
    }

}
