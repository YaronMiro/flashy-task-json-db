<?php

namespace App\Database;

interface DatabaseExporterInterface {    
    /**
     * exportData
     *
     * @param  string $entityName
     * @param  mixed $config
     * @return void
     */
    public function exportData(string $entityName, $config);
}