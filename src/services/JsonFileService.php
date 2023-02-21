<?php

namespace App\Services;

class JsonFileService {
    private $filePath;
  
    public function file($filePath) {
      $this->filePath = trim($filePath);
    }

    public function getFileName() {
      return $this->filePath;
    }

    public function fileExists() {
      return is_file($this->filePath);
    }

    public function deleteFile() {
      if (!is_file($this->filePath)) {
        throw new \Error('File does not exists');
      }
      return unlink($this->filePath);
    }

    public function create($data = []) {
      if (is_file($this->filePath)) {
        throw new \Error('File already exist');
      }

      if (!is_array($data)) {
        throw new \Error('Data must be an array');
      }

      $jsonString = json_encode($data, JSON_PRETTY_PRINT);
      file_put_contents($this->filePath, $jsonString);
    }
    
    public function read($id = null) {
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
        throw new \Error('File does not exist');
      }

      $fileContents = file_get_contents($this->filePath);
      return json_decode($fileContents);
    }

    private function saveJsonDataToFile($jsonData) {
      if (!is_file($this->filePath)) {
        throw new \Error('File does not exist');
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