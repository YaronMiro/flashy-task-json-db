<?php

use PHPUnit\Framework\TestCase;
use App\Database\DatabaseInterface;
use App\Database\DatabaseExporterInterface;
use App\Services\DIContainerService;

class JsonStreamHandler {

    private $filePath;
    private $fileHandle;
    private $buffer;
    private $readCallback;
    private $chunkSize = 8192; // 8KB by default, you can change this to any value
  
    public function __construct($filePath) {
        $this->filePath = $filePath;
    }

    public function create() {
        file_put_contents($this->filePath, '');
    }

    public function parse($readCallback = null) {

        // Exit early in case we can't access the file.
        if (!is_file($this->filePath)) {
            return;
        }

        $this->readCallback = $readCallback;
        $this->fileHandle = fopen($this->filePath, 'r');

        while (!feof($this->fileHandle)) {
            $this->buffer .= fread($this->fileHandle, $this->chunkSize);
            $this->parseBuffer();
        }

        fclose($this->fileHandle);
    }

    public function close(){
        if (is_resource($this->fileHandle)){
            fclose($this->fileHandle);
            $this->fileHandle = null;
            $this->buffer = false;
        }
    }

    private function parseBuffer() {
        while (($pos = strpos($this->buffer, "\n")) !== false) {
            $jsonString = trim(substr($this->buffer, 0, $pos));
            $this->buffer = substr($this->buffer, $pos + 1);

            // Return early in case we have no data, probably last line.
            if (empty($jsonString)) {
                return;
            }
  
            $data = json_decode($jsonString);
            if (!$data) {
                throw new Error("Unable to decode JSON: {$data}");
            }

            call_user_func($this->readCallback, $data);
        }
    }
  
    public function write(object $data) {
        $handle = fopen($this->filePath, 'a');
        fwrite($handle, json_encode($data) . "\n");
        fclose($handle);
    }
  
    public function update($id, $patch) {
      $temp_path = tempnam(dirname($file_path), 'json');
  
      $handler = new JsonStreamHandler($this->fileHandle, function($data) use ($id, $patch, $temp_handle) {
        if ($data['id'] === $id) {
          // Apply the patch to the object
          $patched_data = json_patch($data, $patch);
          fwrite($temp_handle, json_encode($patched_data) . "\n");
        } else {
          // Write the unmodified object to the temp file
          fwrite($temp_handle, json_encode($data) . "\n");
        }
      });
  
      $handler->parse();
  
      fclose($temp_handle);
      unlink($file_path);
      rename($temp_path, $file_path);
    }
  }


class JsonStreamHandlerTest extends TestCase {
    private $filePath = __DIR__ . '/test.json';
    private $fileHandler;
    private $users = [
        ['id' => 1, 'name' => 'Alice', 'age' => 25],
        ['id' => 2, 'name' => 'Bob', 'age' => 30],
        ['id' => 3, 'name' => 'Charlie', 'age' => 35]
    ];

    public function setUp(): void {

        $this->fileHandler = new JsonStreamHandler($this->filePath);
        $this->fileHandler->create();

        foreach ($this->users as $user) {
            $this->fileHandler->write((object) $user);
        }
    }
  
    public function tearDown(): void {
      // Delete the test file
      if (is_file($this->filePath)) {
        unlink($this->filePath);
      }
    }
    
    /**
    * @test
    */
    public function read_all() {
        $users = [];

        $readCallback = function($user) use (&$users) {        
            $users[] = $user;
        };

        $fileHandler = new JsonStreamHandler($this->filePath);
        $fileHandler->parse($readCallback);

        $this->assertCount(3, $users);
        foreach ($users as $index => $user) {
            $this->assertEquals((object) $this->users[$index], $user);
        }
    }

    /**
    * @test
    */
    public function read_one() {
        $targetUser;
        $targetId = 2;
        $fileHandler = new JsonStreamHandler($this->filePath);

        $readCallback = function($user) use (&$targetUser, &$targetId, &$fileHandler) {
            if ($user->id === $targetId) {
                $targetUser = $user;
                $fileHandler->close();
            }
        };

        $fileHandler->parse($readCallback);
        $this->assertEquals((object) $this->users[1], $targetUser);
    }
    
    /**
    * @test
    */
    public function write() {
        $obj = (object) ['id' => 4, 'name' => 'David', 'age' => 40];
        $this->fileHandler->write($obj);
        $lines = file($this->filePath);

        $this->assertCount(4, $lines);
        $this->assertEquals($obj, json_decode($lines[3]));
    }
    
    // public function testUpdate() {
    //   $patch = [
    //     [
    //       "op" => "replace",
    //       "path" => "/name",
    //       "value" => "Bob Jr."
    //     ]
    //   ];
  
    //   JsonStreamHandler::update($this->filePath, 2, $patch);
  
    //   $results = [];
  
    //   $readCallback = function($data) use (&$results) {
    //     $results[] = $data;
    //   };
  
    //   $handler = new JsonStreamHandler($this->filePath, $readCallback);
    //   $handler->parse();
  
    //   $this->assertCount(3, $results);
  
    //   $this->assertEquals(['id' => 1, 'name' => 'Alice', 'age' => 25], $results[0]);
    //   $this->assertEquals(['id' => 2, 'name' => 'Bob Jr.', 'age' => 30], $results[1]);
    //   $this->assertEquals(['id' => 3, 'name' => 'Charlie', 'age' => 35], $results[2]);
    // }
}
