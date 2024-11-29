<?php

namespace Wrk;

use HTTP\HTTPResponses;
use PDO;
use PDOException;

class WrkDatabase {

    private PDO $pdo;

    public function __construct() {
        try {
            $dbUrl = 'mysql:host=' . $_ENV["DB_HOST"] . ';port=' . $_ENV["DB_PORT"] . ';dbname=' . $_ENV["DB_NAME"];
            $this->pdo = new PDO($dbUrl, $_ENV["DB_USER"], $_ENV["DB_PASS"], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch ( PDOException $ex ) {
            HTTPResponses::error(500, $ex->getMessage());
        }
    }

    public function select(string $query, array $params = [], bool $fetchAll = false): array|bool {
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
        return $fetchAll ? $statement->fetchAll(PDO::FETCH_ASSOC) : $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function execute(string $query, array $params = []): bool {
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }

    public function beginTransaction(): void {
        $this->pdo->beginTransaction();
    }

    public function commit(): void {
        $this->pdo->commit();
    }

    public function rollBack(): void {
        $this->pdo->rollBack();
    }

    public function lastInsertId(): bool|string {
        return $this->pdo->lastInsertId();
    }

}