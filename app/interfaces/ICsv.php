<?php

namespace App\interfaces;

interface ICsv
{
    public function readCsv();
    public function exportToCsv($stmt);
}
