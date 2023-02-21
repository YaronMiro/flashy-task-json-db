<?php

namespace App\Services;

use JsonCollectionParser\Parser;
use Error;

class JsonFileService {
    private $filePath;
    private $jsonCollectionParser;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct(Parser $jsonCollectionParser) {
      $this->jsonCollectionParser = $jsonCollectionParser;
    }
  
    /**
     * 
     * Setter for pointing on what file to be used.
     * 
     * @param string $filePath
     * 
     * @return void
     */
    public function file(string $filePath): void {
      $this->filePath = trim($filePath);
    }

    /**
     * 
     * Return the the "current" set file path.
     * 
     * @return string
     */
    public function getFilePath(): string {
      return $this->filePath;
    }

    /**
     * 
     * Check if the "current" set file exists.
     * 
     * @return bool
     */
    public function fileExists(): bool {
      return is_file($this->filePath);
    }

    /**
     * 
     * Delete the "current" set file.
     * 
     * @return bool
     */
    public function deleteFile(): bool {
      $this->ErrorOnFileNotExist();
      return unlink($this->filePath);
    }

   /**
     * 
     * Create a new file according to the "current" set file.
     * 
     * Can also create an empty file or add file content on creation.
     * 
     * @param array $data
     * 
     * @return [type]
     */
    public function create($content = []) {
      if (is_file($this->filePath)) {
        throw new Error('File already exist');
      }

      if (!is_array($content)) {
        throw new Error('Data must be an array');
      }

      $jsonString = json_encode($content, JSON_PRETTY_PRINT);
      file_put_contents($this->filePath, $jsonString);
    }
    
    /**
     * 
     * Read existing file content according to the "current" set file.
     * 
     * By default return the entire file content.
     * Support reading a single record by supplying an "ID" as an argument.
     * 
     * @param null $id
     * 
     * @return array
     */
    public function read($id = null): array {
      $jsonData = $this->getParsedJsonDataFromFile();

      if (!is_null($id)) {
        $jsonData = array_filter($jsonData, function($record) use ($id) {
          return $record->id === $id;
        });
      }

      return count($jsonData) === 1 ? array_values($jsonData): $jsonData;
    }
    
    /**
     * 
     * Add new file content according to the "current" set file.
     * Adds a new single record to at end of the file.
     * 
     * @param mixed $data
     * 
     * @return [type]
     */
    public function add($content) {
      $jsonData = $this->getParsedJsonDataFromFile();
      $jsonData[] = $content;
      $this->saveJsonDataToFile($jsonData);
    }
    
    /**
     * 
     * Add file content according to the "current" set file.
     * Update an existing single record on the file.
     * 
     * @param string $id
     * @param object $content
     * 
     * @return [type]
     */
    public function update(string $id, object $content) {
      $jsonData = $this->getParsedJsonDataFromFile();
      $index = $this->getRecordIndexById($id, $jsonData);

      if (!is_null($index)) {
        $jsonData[$index] = $content;
      }

      $this->saveJsonDataToFile($jsonData);
    }
    
    /**
     * 
     * Add file content according to the "current" set file.
     * Delete an existing single record on the file.
     * 
     * @param string $id
     * 
     * @return [type]
     */
    public function delete(string $id) {
      $jsonData = $this->getParsedJsonDataFromFile();
      $index = $this->getRecordIndexById($id, $jsonData);

      if (!is_null($index)) {
        array_splice($jsonData, $index, 1);
      }

     $this->saveJsonDataToFile($jsonData);
    }

    /**
     * 
     * Read all file content according to the "current" set file.
     * Uses stream to read the data and parse (decode) it to an array of
     * objects.
     * 
     * @return array
     */
    private function getParsedJsonDataFromFile(): array {
      $this->ErrorOnFileNotExist();

      $items = [];
      $this->jsonCollectionParser->parse(
        $this->filePath,
        function (array $item) use (&$items) {
          $items[] = (object) $item;
      });

      return $items;
    }

    /**
     * 
     * Save all file content according to the "current" set file.
     * The data is being encoded as an array of objects.
     * 
     * 
     * @param array $jsonData
     * 
     * @return array
     */
    private function saveJsonDataToFile(array $jsonData) {
      $this->ErrorOnFileNotExist();
      $jsonString = json_encode($jsonData, JSON_PRETTY_PRINT);
      file_put_contents($this->filePath, $jsonString);
    }

    /**
     * 
     * Throw an Error if the file does not exists.
     * 
     * @return [type]
     */
    private function ErrorOnFileNotExist() {
      if (!is_file($this->filePath)) {
        throw new Error('File does not exist');
      }
    }

    /**
     * 
     * Get the record index (position) on the array by filtering according to
     * a given target "ID" and return the index if such record exists, else 
     * return null.
     * 
     * @param string $id
     * @param array $jsonData
     * 
     * @return [type]
     */
    private function getRecordIndexById(string $id, array $jsonData) {
      foreach ($jsonData as $index => $record) {
        if ($record->id === $id) {
          return $index;
        }
      }
      return null;
    }

}