<?php
namespace App\Service;

class DatabaseController extends AbstractController
{
private $databaseService;

public function __construct(DatabaseService $databaseService)
{
$this->databaseService = $databaseService;
}

// ...
}
