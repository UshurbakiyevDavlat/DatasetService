<?php

namespace interfaces;
interface IDb
{
    public function connect(): void;

    public function createTable(): void;

    public function insertDataFromCsv(array $data): void;
}
