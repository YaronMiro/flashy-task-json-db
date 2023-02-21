<?php

use PHPUnit\Framework\TestCase;
use App\Database\DatabaseInterface;
use App\Database\DatabaseExporterInterface;
use App\Services\DIContainerService;

class DatabaseJsonTest extends TestCase {
    private $database;
    private $databaseExporter;
    private $testsRootDirPath = __DIR__;
    private $entitiesNames = [];

    protected function setUp(): void {
        $container = DIContainerService::getInstance()->getContainer();
        $this->database = $container->get(DatabaseInterface::class);
        $this->databaseExporter = $container->get(DatabaseExporterInterface::class);
    }

    /**
    * @test
    */
    public function create_new_entity(): void {
        // Create new "user" entity.
        $userDBModel = $this->createEntity('user');
        $entityFilePath = "{$this->testsRootDirPath}/mocks/{$userDBModel->getFileName()}";

        // Validate that entity was created.
        $this->assertFileExists($entityFilePath);
        $this->assertEquals($userDBModel->getFileName(), "user.json");
        $this->assertTrue($this->database->has('user'));
    }

    /**
    * @test
    */
    public function fail_when_trying_to_create_a_new_entity_with_invalid_name(): void {
        // Check that we get an Exception.
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Entity name is not valid!');
        $this->database->create('1', new stdClass());
    }
    
    /**
    * @test
    */
    public function fail_when_trying_to_create_an_existing_entity(): void {
         // Check that we get an Exception.
         $this->expectException(\Error::class);
         $this->expectExceptionMessage('Entity already exist!');

        // Create new "user" entity.
        $userDBModel = $this->createEntity('user');
    
        // Try to create again the same "user" entity.
        $userDBModel = $this->createEntity('user');
    }

    /**
    * @test
    */
    public function delete_existing_entity(): void {
        // Create new "user" entity.
        $userDBModel = $this->createEntity('user');

        // Delete the "user" entity.
        $entityFilePath = "{$this->testsRootDirPath}/mocks/{$userDBModel->getFileName()}";
        $this->database->drop('user');

        // Validate that entity and file were removed.
        $this->assertFalse($this->database->has('user'));
        $this->assertFalse(is_file($entityFilePath));
    }

    /**
    * @test
    */
    public function use_existing_entities(): void {
        // Create new "user" entity and validate that we can use its model.
        $this->createEntity('user');
        $this->assertEquals($this->database->use('user')->getFileName(), "user.json");

        // Create new "animal" entity and validate that we can use its model.
        $this->createEntity('animal');
        $this->assertEquals($this->database->use('animal')->getFileName(), "animal.json");
    }

    /**
    * @test
    */
    public function insert_valid_record(): void {
        // Create new "user" entity.
        $userDBModel = $this->createEntity('user');
        $newUser = (object) ['name' => 'Yaron', 'age' => 35];
        $record = $userDBModel->insert($newUser);

        // Validate that a new record was added successfully.
        $this->assertEquals(reset($userDBModel->find($record->id)), $record);
        $this->assertArrayHasKey('id', (array) $record);
        $this->assertArrayHasKey('creationTime', (array) $record);
    }

    /**
    * @test
    */
    public function fail_insert_non_valid_record(): void {
        // Check that we get an Exception.
        $this->expectException(\Error::class);

        // Create new "user" entity.
        $userDBModel = $this->createEntity('user');
        $newUser = (object) ['firstName' => 'Yaron', 'age' => 35];
        $userDBModel->insert($newUser);
    }

    /**
    * @test
    */
    public function get_record_by_id(): void {
        $userDBModel = $this->createEntity('user');
        $user = $userDBModel->insert((object) ['name' => 'Yaron', 'age' => 35]);
        $record = $userDBModel->find($user->id)[0];

        // Validate that record we got the same record.
        $this->assertIsObject($record);
        $this->assertEquals($user->id, $record->id);
    }

    /**
    * @test
    */
    public function update_record_by_id(): void {
        $userDBModel = $this->createEntity('user');
        $record = $userDBModel->insert((object) ['name' => 'Yaron', 'age' => 35]);
        $record->name = 'Yaron Miro';
        $updatedRecord = $userDBModel->update($record->id, $record);

        // Validate that the record was updated successfully.
        $this->assertIsObject($updatedRecord);
        $this->assertEquals($updatedRecord->id, $record->id);
        $this->assertEquals($updatedRecord->name, $record->name);
        $this->assertArrayHasKey('updateTime', (array) $updatedRecord);
    }

    /**
    * @test
    */
    public function fail_update_non_valid_record(): void {
         // Check that we get an Exception.
         $this->expectException(\Error::class);

        $userDBModel = $this->createEntity('user');
        $record = $userDBModel->insert((object) ['firstName' => 'Yaron', 'age' => 35]);
        $record->name = 'Yaron Miro';
        $updatedRecord = $userDBModel->update($record->id, $record);
    }

    /**
    * @test
    */
    public function delete_record(): void {
        $userDBModel = $this->createEntity('user');
        $record = $userDBModel->insert((object) ['name' => 'Yaron', 'age' => 35]);

        // Validate that the record was deleted successfully.
        $this->assertEquals($userDBModel->find($record->id)[0], $record);
        $userDBModel->delete($record->id);
        $this->assertEquals($userDBModel->find($record->id), []);
    }

    /**
    * @test
    */
    public function count_existing_records(): void {
        // Create new "user" entity and add 2 records.
        $userDBModel = $this->createEntity('user');
        $userDBModel->insert((object) ['name' => 'Yaron', 'age' => 35]);
        $userDBModel->insert((object) ['name' => 'Lena', 'age' => 33]);

        // Validate that a new record was added successfully.
        $this->assertEquals(2, $userDBModel->count());
    }

    private function createEntity($entityName) {
        // Get the "user" entity schema definition.
        $fileContents = file_get_contents(
            $this->testsRootDirPath . "/schemas/{$entityName}-entity-schema.json"
        );

        $schema = json_decode($fileContents);

        // Create new entity.
        $this->entitiesNames[] = $entityName;
        return $this->database->create($entityName, $schema);
    }

    protected function tearDown(): void {
        // Clear DB entities once test has ended.
        foreach ($this->entitiesNames as $entityName) {
            if ($entityName !== '' && $this->database->has($entityName)) {
                $this->database->drop($entityName);
            }
        }
      }
}
