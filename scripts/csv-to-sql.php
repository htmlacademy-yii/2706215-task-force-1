<?php

declare(strict_types=1);

use Sanweb\Taskforce\components\CsvToSqlConverter\CsvToSqlConverter;

$projectRoot = dirname(__DIR__);

require_once $projectRoot . '/vendor/autoload.php';

$conversions = [
    [
        'csv' => $projectRoot . '/data/csv/categories.csv',
        'sql' => $projectRoot . '/data/sql/categories.sql',
        'table' => 'category',
        'fields' => [
            'name' => 'name',
            'icon' => 'slug',
        ],
    ],
    [
        'csv' => $projectRoot . '/data/csv/cities.csv',
        'sql' => $projectRoot . '/data/sql/cities.sql',
        'table' => 'city',
        'fields' => [
            'name' => 'name',
            'lat' => 'lat',
            'long' => 'lng',
        ],
    ],
];

foreach ($conversions as $conversion) {
    $converter = new CsvToSqlConverter($conversion['csv']);

    $converter->convert(
        outputFile: $conversion['sql'],
        table: $conversion['table'],
        fields: $conversion['fields'],
    );

    echo "Created: {$conversion['sql']}" . PHP_EOL;
}
