<?php

namespace App\services;

use App\interfaces\IDb;
use Memcached;
use PDO;
use PDOException;

class DbService implements IDb
{
    private PDO $pdo;
    private CsvService $csvService;

    public function __construct()
    {
        $host = getenv('DB_HOST');
        $database = getenv('DB_NAME');
        $username = getenv('DB_USER');
        $password = getenv('DB_PASS');
        $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";

        $this->csvService = new CsvService();
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
            $this->getTableViewPagination();
        }
    }

    public function getTableViewPagination(): void
    {
        $category_filter = $_GET['category'] ?? null;
        $gender_filter = $_GET['gender'] ?? null;
        $birthdate_filter = $_GET['birthdate'] ?? null;
        $age_range_filter = $_GET['age_range'] ?? null;
        $export = $_GET['export'] ?? null;

// Get the current page number
        $current_page = $_GET['page'] ?? 1;

// Set the number of records to display per page
        $records_per_page = 10;

        // get the total number of rows
        $totalRows = $this->pdo->query('SELECT count(*) FROM goods')->fetchColumn();

// calculate the total number of pages
        $totalPages = ceil($totalRows / $records_per_page);

// Calculate the offset value
        $offset = ($current_page - 1) * $records_per_page;

        $sql = 'SELECT * FROM goods';
        $where_conditions = [];
        $params = [];

// Retrieve the records for the current page
        if (!is_null($category_filter)) {
            $where_conditions[] = 'category = :category';
        }

        if (!is_null($gender_filter)) {
            $where_conditions[] = 'gender = :gender';
        }

        if (!is_null($birthdate_filter)) {
            $where_conditions[] = 'birthDate = :birthdate';
        }

        if (!is_null($age_range_filter)) {
            $age_range = explode('-', $age_range_filter);
            $where_conditions[] = 'birthDate BETWEEN DATE_SUB(NOW(), INTERVAL :max_age YEAR) AND DATE_SUB(NOW(), INTERVAL :min_age YEAR)';
        }
        if (!empty($where_conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $where_conditions);
        }

// Prepare the statement and execute it with parameters

        $sql .= ' LIMIT :offset, :limit';
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);

        if (!is_null($category_filter)) {
            $stmt->bindValue(':category', $category_filter);
        }
        if (!is_null($gender_filter)) {
            $stmt->bindValue(':gender', $gender_filter);
        }
        if (!is_null($birthdate_filter)) {
            $stmt->bindValue(':birthdate', $birthdate_filter);
        }
        if (!is_null($age_range_filter)) {
            $stmt->bindValue(':min_age', $age_range[0]);
            $stmt->bindValue(':max_age', $age_range[1]);
        }

        $stmt->execute();
        if ($export) {
            $this->csvService->exportToCsv($stmt);
        }

// Display the records in a table
        echo '<table>';
        echo '<tr><th>ID</th><th>Category</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Gender</th><th>Birth Date</th></tr>';
        $params = [
            'export' => 1,
            'gender' => $gender_filter ?? null,
            'category' => $category_filter ?? null,
            'birthdate' => $birthdate_filter ?? null,
            'age_range' => $age_range_filter ?? null,
            'page' => $current_page ?? null
        ];
        $query_string = http_build_query($params);
        echo "<a class='btn' href='?$query_string'> Export to CSV</a>";

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['id']) . '</td>';
            echo '<td>' . htmlspecialchars($row['category']) . '</td>';
            echo '<td>' . htmlspecialchars($row['firstname']) . '</td>';
            echo '<td>' . htmlspecialchars($row['lastname']) . '</td>';
            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
            echo '<td>' . htmlspecialchars($row['gender']) . '</td>';
            echo '<td>' . htmlspecialchars($row['birthDate']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';

// display the pagination links
        echo '<div class="pagination">';
        if ($totalPages > 1) {
            $startPage = max(1, $current_page - 5);
            $endPage = min($totalPages, $current_page + 5);
            if ($startPage > 1) {
                echo '<a href="?page=1">1</a> ... ';
            }
            for ($i = $startPage; $i <= $endPage; $i++) {
                if ($i == $current_page) {
                    echo '<span class="current">' . $i . ' ' . '</span>';
                } else {
                    echo '<a href="?page=' . $i . '">' . $i . ' ' . '</a>';
                }
            }
            if ($endPage < $totalPages) {
                echo ' ... <a href="?page=' . $totalPages . '">' . $totalPages . '</a>';
            }
        }
        echo '</div>';

    }
}
