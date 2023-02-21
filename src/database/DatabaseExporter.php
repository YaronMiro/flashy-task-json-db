<?php

namespace App\Database;

use App\Database\DatabaseExporterInterface;
use App\Database\DatabaseInterface;

/**
 * Database data exporter.
 */
class DatabaseExporter implements DatabaseExporterInterface {

    private $database;
    private $data;
    
    /**
     * __construct
     *
     * @param  mixed $database
     * @return void
     */
    public function __construct(DatabaseInterface $database) {
        $this->database = $database;
    }
    
    /**
     * The main export data function.
     *
     * @param  string $entityName
     * @param  array $projection
     * @return array
     */
    public function exportData(string $entityName, $projection = []): array {
        $this->data = [];

        if ($this->database->has($entityName) && is_array($projection)) {
            // Get all records of the entity with filtered fields.
            $this->data = $this->database
                ->use($entityName)
                ->find(null, $projection);
        }

        return $this->data;
    }
}