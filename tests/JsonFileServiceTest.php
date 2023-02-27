<?php

use PHPUnit\Framework\TestCase;
use App\Services\JsonFileService;
use App\Services\DIContainerService;

class JsonFileServiceTest extends TestCase {
  private $fileName;
  private $jsonCRUD;
  private $mocksDirPath = __DIR__ . '/mocks';
  private $records = [];
  private $jsonCollectionParser;

  protected function setUp(): void {
    $container = DIContainerService::getInstance()->getContainer();
    $this->jsonCRUD = $container->get(JsonFileService::class);
    $fileContents = file_get_contents($this->mocksDirPath . '/user-mock.json');
    $this->records = json_decode($fileContents);
  }

  /**
  * @test
  */
  public function create_file_without_data(): void {
    $this->fileName = $this->mocksDirPath . '/tmp/create-without-data.json';
    $this->jsonCRUD->file($this->fileName);

    $this->jsonCRUD->create();
    $jsonData = $this->jsonCRUD->read();

    $this->assertCount(0, $jsonData);
    $this->assertEquals($jsonData, []);
  }

  /**
  * @test
  */
  public function create_file_with_data(): void {
    $this->fileName = $this->mocksDirPath . '/tmp/create-with-data.json';
    $this->jsonCRUD->file($this->fileName);

    $this->jsonCRUD->create($this->records);
    $jsonData = $this->jsonCRUD->read();
    $this->assertCount(3, $jsonData);
    $this->assertEquals($jsonData, $this->records);
  }

  /**
  * @test
  */
  public function delete_file(): void {
    $this->useMockTestJsonInstance();
    $this->assertTrue($this->jsonCRUD->fileExists());

    $this->jsonCRUD->deleteFile();
    $this->assertFalse($this->jsonCRUD->fileExists());
  }

  /**
  * @test
  */
  public function read_all_items(): void {
    $this->useMockTestJsonInstance();
    $this->assertEquals($this->records, $this->jsonCRUD->read());
  }

  /**
  * @test
  */
  public function read_one_item(): void {
    $this->useMockTestJsonInstance();
    $records = $this->jsonCRUD->read("1");
    $this->assertEquals($this->records[0], $records[0]);
    $this->assertCount(1, $records);
  }

  /**
  * @test
  */
  public function add_one_item(): void {
    $this->useMockTestJsonInstance();
    $record = (object) ['id' => "4", 'name' => 'Alex', 'age' => 3];
    $this->jsonCRUD->add($record);
    $data = $this->jsonCRUD->read();
    $this->assertCount(4, $data);
    $this->assertEquals($record, $this->jsonCRUD->read("4")[0]);
  }

  /**
  * @test
  */
  public function update_one_item(): void {
    $this->useMockTestJsonInstance();
    $newRecord = (object) ['id' => "1", 'name' => 'alma', 'age' => 4.5];
    $this->jsonCRUD->update("1", $newRecord);
    $this->assertCount(3, $this->jsonCRUD->read());
    $this->assertEquals($newRecord, $this->jsonCRUD->read("1")[0]);
  }

  /**
  * @test
  */
  public function delete_one_item(): void {
    $this->useMockTestJsonInstance();
    $this->jsonCRUD->delete("1");
    $data = $this->jsonCRUD->read();
    $expectedData = [
      $this->records[1],
      $this->records[2],
    ];
    $this->assertCount(2, $this->jsonCRUD->read());
    $this->assertEquals($expectedData, $data);
  }

  private function useMockTestJsonInstance(): void {
    $sourceFile = $this->mocksDirPath . '/user-mock.json';
    $destinationFile = $this->mocksDirPath . '/tmp/user-mock.json';
    copy($sourceFile, $destinationFile);
    $this->fileName = $destinationFile;
    $this->jsonCRUD->file($this->fileName);
  }

  protected function tearDown(): void {
    // Clear json file once test has ended.
    if ($this->jsonCRUD->fileExists()) {
      $this->jsonCRUD->deleteFile();
    }
  }
}
