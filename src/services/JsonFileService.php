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
  
    public function file($filePath): void {
      $this->filePath = trim($filePath);
    }

    public function getFileName(): string {
      return $this->filePath;
    }

    public function fileExists(): bool {
      return is_file($this->filePath);
    }

    public function deleteFile(): bool {
      if (!is_file($this->filePath)) {
        throw new Error('File does not exists');
      }
      return unlink($this->filePath);
    }

    public function create($data = []) {
      if (is_file($this->filePath)) {
        throw new Error('File already exist');
      }

      if (!is_array($data)) {
        throw new Error('Data must be an array');
      }

      $jsonString = json_encode($data, JSON_PRETTY_PRINT);
      file_put_contents($this->filePath, $jsonString);
    }
    
    public function read($id = null): array {
      $jsonData = $this->getParsedJsonDataFromFile();

      if (!is_null($id)) {
        $jsonData = array_filter($jsonData, function($record) use ($id) {
          return $record->id === $id;
        });
      }

      return count($jsonData) === 1 ? array_values($jsonData): $jsonData;
    }
    
    public function add($data) {
      $jsonData = $this->getParsedJsonDataFromFile();
      $jsonData[] = $data;
      $this->saveJsonDataToFile($jsonData);
    }
    
    public function update($id, $data) {
      $jsonData = $this->getParsedJsonDataFromFile();
      $index = $this->getRecordIndexById($id, $jsonData);

      if (!is_null($index)) {
        $jsonData[$index] = $data;
      }

      $this->saveJsonDataToFile($jsonData);
    }
    
    public function delete($id) {
      $jsonData = $this->getParsedJsonDataFromFile();
      $index = $this->getRecordIndexById($id, $jsonData);

      if (!is_null($index)) {
        array_splice($jsonData, $index, 1);
      }

     $this->saveJsonDataToFile($jsonData);
    }

    private function getParsedJsonDataFromFile() {
      if (!is_file($this->filePath)) {
        throw new Error('File does not exist');
      }

      $items = [];
      $this->jsonCollectionParser->parse(
        $this->filePath,
        function (array $item) use (&$items) {
          $items[] = (object) $item;
      });

      return $items;
    }

    private function saveJsonDataToFile($jsonData) {
      if (!is_file($this->filePath)) {
        throw new Error('File does not exist');
      }

      $jsonString = json_encode($jsonData, JSON_PRETTY_PRINT);
      file_put_contents($this->filePath, $jsonString);
    }

    private function getRecordIndexById($id, $jsonData) {
      foreach ($jsonData as $index => $record) {
        if ($record->id === $id) {
          return $index;
        }
      }
      return null;
    }

}