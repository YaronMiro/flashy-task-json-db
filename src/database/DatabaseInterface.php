<?php

namespace App\Database;


interface DatabaseInterface {    
    /**
     * create
     *
     * @param  string $entityName
     * @param  object $schema
     * @return void
     */
    public function create(string $entityName, object $schema);
    
    /**
     * use
     *
     * @param  string $entityName
     * @return void
     */
    public function use(string $entityName);
    
    /**
     * has
     *
     * @param  string $entityName
     * @return void
     */
    public function has(string $entityName);
    
    /**
     * drop
     *
     * @param  string $entityName
     * @return void
     */
    public function drop(string $entityName);
}