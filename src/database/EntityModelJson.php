<?php

namespace App\Database;

use App\Database\EntityModelInterface;
use App\Services\JsonFileService;
use App\Services\UtilsService;
use JsonSchema\Validator;
use Error;
use stdClass;

/**
 * The "Entity" Model.
 */
class EntityModelJson implements EntityModelInterface {
    private $jsonCRUD;
    private $validator;
    private $schema;
    private $utils;
    private $preservedFields = ['id', 'creationTime'];
    
    /**
     * __construct
     *
     * @return void
     */
    public function __construct(
        object $schema,
        JsonFileService $jsonFileService,
        Validator $validator,
        UtilsService $utilsService
    ) {
        $this->validator = $validator;
        $this->jsonCRUD = $jsonFileService;
        $this->utils = $utilsService;

        $this->validateNewEntity($schema);
        $this->schema = $schema;
        $this->filePath = $schema->filePath;
        
        $this->create();
    }


    /**
     * 
     * Get the entity file name.
     * 
     * @return string
     */
    public function getFileName(): string {
        return basename($this->filePath);
    }


    /**
     * 
     * Count the "Entity" number of records.
     * 
     * @return int
     */
    public function count(): int {
        // Count the number of existing records.
        return count($this->find());
    }


    /**
     * 
     * Get the "Entity" records.
     * 
     * Support getting all the records or a single record.
     * The return record fields can also be filtered by passing a whitelist
     * of fields.
     * 
     * @param string $id
     * @param array $projection
     * 
     * @return array
     */
    public function find($id = '', array $projection = []): array {
        $records = [];

        // In the case we have a single object.
        if (!empty($id) && is_string($id)) {
            $records = $this->jsonCRUD->read($id);
        // In case we want all existing records.
        } else {
            $records = $this->jsonCRUD->read();
        }

        // Filter out fields according to a whitelist.
        if (is_array($projection) && count($projection) > 0) {
            
            $formattedRecords = [];
            foreach ($records as $record) {
                $filteredObject = new stdClass();
                foreach ($projection as $field) {
                    if (property_exists($record, $field)) {
                        $filteredObject->{$field} = $record->{$field};
                    }
                }
                $formattedRecords[] = $filteredObject;
            }
            $records = $formattedRecords;

        }
            
        return $records;
    }

    /**
     * 
     * Insert's a new record.
     * 
     * @param object $record
     * 
     * @return object
     */
    public function insert(object $record): object {
        // Validate new data matches the entity schema.
        $this->validateRecord($record);

        // Add preserved fields data.
        $record->id = $this->utils::generateUniqueId();
        $record->creationTime = time();

        // insert new record.
        $this->jsonCRUD->add($record);

        return $record;
    }

    /**
     * 
     * Update a single record with new data.
     *
     * support partial fields update.
     * 
     * @param string $id
     * @param object $record
     * 
     * @return [type]
     */
    public function update(string $id, object $record) {
        // Validate new data matches the entity schema.
        $this->validateRecord($record);
        
        // Get the original record data.
        $originalRecord = $this->jsonCRUD->read($id)[0];

        // Merge original record data with the new data.
        $updatedRecord = $this->utils::mergeObjectsRecursive(
            $originalRecord,
            $record
        );

        // // Add new data to the record.
        $updatedRecord->updateTime = time();

        // Restore preserved fields, so they are never overwritten.
        foreach ($this->preservedFields as $fieldName) {
            $updatedRecord->{$fieldName} = $originalRecord->{$fieldName};
        }

         // insert updated record.
        $this->jsonCRUD->update($id, $updatedRecord);
        return $updatedRecord;
    }
    
    /**
     * 
     * Deletes a single record.
     * 
     * @param string $id
     * 
     * @return [type]
     */
    public function delete(string $id) {
        $this->jsonCRUD->delete($id);
    }



    /**
     * 
     * Validate a given record data with the "Entity" schema-definition".
     * 
     * @param object $object
     * 
     * @return [type]
     */
    private function validateRecord(object $object) {
        $this->validator->validate($object, $this->schema->definition);

        if (!$this->validator->isValid()) {
            $error = $this->validator->getErrors()[0];
            $property = $error['property'] === ''
                ? '- '
                : "property \"{$error['property']}\", ";

            throw new Error(sprintf(
                '"Entity Definition Schema" %s%s', $property, $error['message'])
            );
        }
    }

    /**
     * 
     * Validate Entity" schema.
     * 
     * @param object $entity
     * 
     * @return [type]
     */
    private function validateNewEntity(object $entity) {
        $baseEntitySchema = (object) [
            "type" => "object",
                "properties" => (object) [
                    "filePath" => (object) [
                        "type" => "string", 
                        "description" => "The entity unique file path",
                        "required" => true,
                    ],
                    "definition" => (object) [
                        "type" => "object", 
                        "description" => "The entity object definition",
                        "required" => true,
                    ],
                ],
            ];

        $this->validator->validate($entity, $baseEntitySchema);
        if (!$this->validator->isValid()) {
            $error = $this->validator->getErrors()[0];
            $format = 'Entity schema property "%s", %s';
            throw new Error(sprintf($format, $error['property'], $error['message']));
        }
    }

    /**
     * @param object $record
     * 
     * @return string
     */
    private function generateUniqueRecordId(object $record): string {
        return $this->utils->generateUniqueId();
    }


    /**
     * 
     * Adds a new JSON file as the "Entity" main storage.
     * 
     * @return [type]
     */
    private function create() {
        $this->jsonCRUD->file($this->filePath);
        $this->jsonCRUD->create();
    }
}