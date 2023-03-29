<?php

namespace services;

use interfaces\ICsv;

class CsvService implements ICsv
{
    public function readCsv(): array|string
    {
        $file = fopen('dataset.csv', 'rb');

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
}
