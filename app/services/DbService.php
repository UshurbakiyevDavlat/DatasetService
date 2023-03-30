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
                `gender` varchar(255) NOT NULL,
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
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM goods');
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count === '0') {
            // Prepare the SQL statement to insert the data
            // Loop through the data and execute the SQL statement for each row
            // Define the chunk size

            $chunkSize = 500;

            $stmt = $this->pdo
                ->prepare('INSERT INTO goods (category, firstname, lastname, email, gender, birthDate) 
                VALUES (:category, :firstname, :lastname, :email, :gender, :birthDate)');

            // Loop through the data in chunks
            $dataChunks = array_chunk($data, $chunkSize);

            foreach ($dataChunks as $chunk) {
                $this->pdo->beginTransaction();
                try {
                    foreach ($chunk as $row) {
                        $values = array_map('trim', explode(',', array_values($row)[0]));

                        $stmt->bindValue(':category', $values[0]);
                        $stmt->bindValue(':firstname', $values[1]);
                        $stmt->bindValue(':lastname', $values[2]);
                        $stmt->bindValue(':email', $values[3]);
                        $stmt->bindValue(':gender', $values[4]);
                        $stmt->bindValue(':birthDate', $values[5]);

                        $stmt->execute();
                    }
                    $this->pdo->commit();
                } catch (PDOException $e) {
                    $this->pdo->rollback();
                    echo 'Error inserting row into database: ' . $e->getMessage();
                    exit();
                }
            }

            echo 'Data inserted successfully' . PHP_EOL;

        } else {
            echo 'Table already has data from csv file';
        }
    }
}
