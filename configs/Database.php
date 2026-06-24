<?php

namespace configs;

use PDO;
use App\Utils\Utility;

class Database
{
    private static $instance = null;
    private PDO $connection;

    private function __construct()
    {
        $host = $_ENV['DB_HOST'];
        $dbname = $_ENV['DB_NAME'];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];

        try {

            $this->connection = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password
            );

            $this->connection->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            $this->connection->setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE,
                PDO::FETCH_ASSOC
            );

        } catch (\PDOException $e) {
            Utility::log("DB Connection Failed: " . $e->getMessage(), 'error', 'DB::Constructor', ['host' => 'localhost'], $e);
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function connect()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance->connection;
    }
}
/**!USAGE
 * 
 * $db = Database::connect();

    $stmt = $db->query("SELECT * FROM books");
    $books = $stmt->fetchAll();
 */