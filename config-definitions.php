<?php

use App\Services\DIContainerService;
use App\Services\JsonFileService;
use App\Database\DatabaseJsonFile;
use App\Database\DatabaseInterface;
use App\Database\EntityModelInterface;
use App\Database\EntityModelJson;
use App\Database\DatabaseExporterInterface;
use App\Database\DatabaseExporter;
use JsonSchema\Validator;

return [
    DatabaseInterface::class => DI\create(DatabaseJsonFile::class)
        ->constructor(
            __DIR__ . '/tests/mocks',
            DI\autowire(JsonFileService::class),
            DI\create(Validator::class),
            DI\factory([DIContainerService::class, 'getInstance']
        ),
    ),
    EntityModelInterface::class => DI\autowire(EntityModelJson::class)
        ->constructorParameter('jsonFileService', DI\autowire(JsonFileService::class))
        ->constructorParameter('validator', DI\create(Validator::class)
    ),
    DatabaseExporterInterface::class => DI\autowire(DatabaseExporter::class),
];