<?php

namespace App\services;

use App\interfaces\ICsv;
use PDO;

class CsvService implements ICsv
{
    public function readCsv(): array|string
    {
        $file = fopen(__DIR__ . '/../../storage/dataset.csv', 'rb');

        if ($file !== false) {
            $headers = fgetcsv($file); // Read the headers

            $data = [];

            while (($row = fgetcsv($file)) !== false) {
                $data[] = array_combine($headers, $row);
            }

            fclose($file);

            return $data;

            // Now $data is an array of associative arrays, with each row of the CSV file as an element in the array
        }

        // Handle error opening file
        $error = error_get_last();
        echo "Error opening CSV file: {$error['message']}";
        return [];
    }

    public function exportToCsv($stmt): void
    {
        // Set the HTTP headers to force the file download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=export.csv');

        // Create a file pointer connected to the output stream
        $output = fopen('php://output', 'wb');

        // Write the CSV headers
        fputcsv($output, ['ID', 'Category', 'First Name', 'Last Name', 'Email', 'Gender', 'Birth Date']);

        // Write the CSV data
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $rows);
        }

        // Close the file pointer
        fclose($output);
        exit();
    }
}
