<?php

namespace App\Database;
use App\Database\DatabaseInterface;
use App\Database\EntityModelInterface;
use App\Database\EntityModelJson;
use App\Services\DIContainerService;
use App\Services\JsonFileService;
use JsonSchema\Validator;
use stdClass;
use Error;

/**
 * Database in the form of JSON files as storage.
 */
class DatabaseJsonFile implements DatabaseInterface {
    private $sourceDirectory;
    private $entities;
    private $jsonCRUD;
    private $validator;
    
    /**
     * __construct
     *
     * @return void
     */
    public function __construct(
        string $sourceDirectory,
        JsonFileService $jsonFileService,
        Validator $validator,
        DIContainerService $DIContainer)
    {
        $this->sourceDirectory = $sourceDirectory;
        $this->jsonCRUD = $jsonFileService;
        $this->validator = $validator;
        $this->DIContainer = $DIContainer;
        $this->entities = new stdClass();
        $this->container = DIContainerService::getInstance()->getContainer();
    }
    

    /**
     * 
     * Create a new "Entity".
     * 
     * @param string $entityName
     * @param object $schema
     * 
     * @return EntityModelInterface
     */
    public function create(string $entityName, object $schema) {
        $this->validateEntityRegistry(trim($entityName));
        return $this->registerEntity($entityName, $schema)->model;
    }

    /**
     * 
     * Checks if a given "Entity" exists.
     * 
     * @param string $entityName
     * 
     * @return bool
     */
    public function has(string $entityName): bool {
        return isset($this->entities->{$entityName});
    }

    /**
     * 
     * Return's a given "Entity" model instance.
     * 
     * @param string $entityName
     * 
     * @return EntityModelInterface
     */
    public function use(string $entityName): EntityModelInterface {
        if ($entity = $this->get($entityName)) {
            return $entity->model;
        }
    }

    /**
     * 
     * Drop the "Entity" registry and also delete the JSON file.
     * 
     * @param string $entityName
     * 
     * @return bool
     */
    public function drop(string $entityName): bool {
        if (!$this->has($entityName)) {
            throw new Error("Entity does NOT exist!");
        }

        $filePath = $this->get($entityName)->filePath;
        $this->jsonCRUD->file($filePath);
        $this->jsonCRUD->deleteFile();

        unset($this->entities->{$entityName});
        return true;
    }

    /**
     * 
     * Return's the main root directory that stores all json files.
     * 
     * @return string
     */
    public function getSourceDirectoryPath(): string {
       return $this->sourceDirectory;
    }
    
    /**
     * 
     * Return's a given "Entity" registry object.
     * 
     * @param string $entityName
     * 
     * @return [type]
     */
    private function get(string $entityName) {
        if (isset($this->entities->{$entityName})){
            return $this->entities->{$entityName};
        };

        throw new Error("Entity does not exists");
    }

    /**
     * 
     * * Validate "Entity" registry schema.
     * 
     * Entity name must be a non empty string in the format of "Kebabcase".
     * Allows only [a-z] or "-".
     *
     * @param string $entityName
     * 
     * @return void
     */
    private function validateEntityRegistry(string $entityName): void {

        // Validate if the entity name matches the pattern.
        
        if( !is_string($entityName)
            || $entityName === ''
            || !preg_match('/^[a-z]+(-[a-z]+)*$/', $entityName)
        ) {
            throw new Error("Entity name is not valid!");
        }

        // Validate if the entity exists already.
        if ($this->has($entityName)) {
            throw new Error("Entity already exist!");
        }
    }

    /**
     * 
     * Register the entity and instantiates the "Entity Model".
     * 
     * @param string $entityName
     * @param object $schema
     * 
     * @return [type]
     */
    private function registerEntity(string $entityName, object $schema) {
        $entityMetaData = new stdClass();
        $entityMetaData->name = $entityName;
        $entityMetaData->filePath = $this->sourceDirectory . "/{$entityMetaData->name}.json";
        $entityMetaData->definition = $schema;

        $entityMetaData->model = $this->container->make(
            EntityModelInterface::class,
            ['schema' => $entityMetaData]
        );
        $this->entities->{$entityMetaData->name} = $entityMetaData;
        return $this->entities->{$entityMetaData->name};
    }
}