<?php

use PHPUnit\Framework\TestCase;
use App\Database\DatabaseInterface;
use App\Database\DatabaseExporterInterface;
use App\Services\DIContainerService;

class DatabaseExporterTest extends TestCase {
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
    public function export_existing_entity_records(): void {
        // Create new "user" entity and add 4 records.
        $entityName = 'user';
        $userDBModel = $this->createEntity($entityName);
        $userDBModel->insert((object) ['name' => 'Yaron', 'age' => 35]);

        // Get exported data with only the "whitelist" fields.
        $whitelistedFields = ['id', 'name'];
        $record = (array) reset($this->databaseExporter->exportData(
            $entityName,
            $whitelistedFields)
        );
      
        // Validate that we don't have unexpected fields. First by checking
        // that all the expected fields exists, and then by counting the total
        // number of existing fields and comparing it to the total number of
        // expected fields.
        $this->assertArrayHasKey($whitelistedFields[0], $record);
        $this->assertArrayHasKey($whitelistedFields[1], $record);
        $this->assertEquals(count($whitelistedFields), count($record));
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
