<?php
namespace App;

use App\services\DbService;
use App\services\CsvService;

class Main {
    private DbService $dbService;
    private CsvService $csvService;

    public function __construct() {
        $this->dbService = new DbService();
        $this->csvService = new CsvService();
    }

    public function run(): void
    {
        $this->dbService->connect();
        $this->dbService->createTable();
        $this->dbService->insertDataFromCsv($this->csvService->readCsv());
    }
}
