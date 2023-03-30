<?php

namespace App\services;

use App\interfaces\IDb;
use PDO;
use PDOException;

class DbService implements IDb
{
    private PDO $pdo;

    public function __construct()
    {
        $host = getenv('DB_HOST');
        $database = getenv('DB_NAME');
        $username = getenv('DB_USER');
        $password = getenv('DB_PASS');
        $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";

        $this->pdo = new PDO($dsn, $username, $password);
    }

    public function connect(): void
    {
        try {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable error reporting
        } catch (PDOException $e) {
            echo 'Error connecting to database: ' . $e->getMessage();
            exit();
        }
    }

    public function createTable(): void
    {
        try {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS `goods` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `category` varchar(255) NOT NULL,
                `firstname` varchar(255) NOT NULL,
                `lastname` varchar(255) NOT NULL,
                `email` varchar(255) NOT NULL,
                `birthDate` varchar(255) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        } catch (PDOException $e) {
            echo 'Error creating table: ' . $e->getMessage();
            exit();
        }
    }

    public function insertDataFromCsv(array $data): void
    {
        // Prepare the SQL statement to insert the data
        $stmt = $this->pdo
            ->prepare('INSERT INTO goods (category, firstname, lastname, email, gender, birthDate) 
                            VALUES (:category, :firstname, :lastname, :email, :gender, :birthDate)');

// Loop through the data and execute the SQL statement for each row
        foreach ($data as $row) {
            try {
                $stmt->execute([
                    ':category' => $row['category'],
                    ':firstname' => $row['firstname'],
                    ':lastname' => $row['lastname'],
                    ':email' => $row['email'],
                    ':gender' => $row['gender'],
                    ':birthDate' => $row['birthDate']
                ]);
            } catch (PDOException $e) {
                echo 'Error inserting row into database: ' . $e->getMessage();
                exit();
            }
        }
    }
}
