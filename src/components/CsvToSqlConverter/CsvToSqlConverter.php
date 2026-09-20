<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\components\CsvToSqlConverter;

use InvalidArgumentException;
use Sanweb\Taskforce\exception\FileException;
use SplFileObject;

/**
 * Converts CSV data to SQL INSERT statements.
 */
final class CsvToSqlConverter
{
    private SplFileObject $reader;

    /** @var array{delimiter: string, enclosure: string, escape: string} */
    private array $options = [
        'delimiter' => ',',
        'enclosure' => '"',
        'escape' => '',
    ];

    /**
     * @param array{delimiter?: string, enclosure?: string, escape?: string} $options
     */
    public function __construct(
        string $file,
        array $options = []
    ) {
        $this->options = array_replace($this->options, $options);

        $this->validateFile($file);

        $this->reader = $this->createReader($file);
    }

    /**
     * Validates the input file.
     */
    private function validateFile(string $file): void
    {
        if (!is_file($file)) {
            throw new FileException("File $file not found.");
        }

        if (!is_readable($file)) {
            throw new FileException("File $file is not readable.");
        }

        if (!$this->isUtf8($file)) {
            throw new FileException("File $file must be encoded in UTF-8");
        }
    }

    /**
     * Creates and configures the CSV reader.
     */
    private function createReader(string $file): SplFileObject
    {
        $reader = new SplFileObject($file, 'rb');

        $reader->setFlags(
            SplFileObject::READ_CSV
                | SplFileObject::SKIP_EMPTY
                | SplFileObject::READ_AHEAD
                | SplFileObject::DROP_NEW_LINE,
        );

        $reader->setCsvControl(
            $this->options['delimiter'],
            $this->options['enclosure'],
            $this->options['escape'],
        );

        return $reader;
    }

    /**
     * Checks whether the file uses UTF-8.
     */
    private function isUtf8(string $file): bool
    {
        $reader = new SplFileObject($file, 'rb');

        foreach ($reader as $line) {
            if (!mb_check_encoding($line, 'UTF-8')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Yields non-empty CSV rows and removes a leading UTF-8 BOM.
     *
     * @return iterable<int, array<int, string|null>>
     */
    public function read(): iterable
    {
        $this->reader->rewind();

        $isFirstRow = true;

        foreach ($this->reader as $row) {
            if (!is_array($row) || $this->isEmptyRow($row)) {
                continue;
            }

            if ($isFirstRow) {
                $row = $this->removeBom($row);
                $isFirstRow = false;
            }

            yield $row;
        }
    }

    /**
     * @param array<int, string|null> $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim($value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, string|null> $row
     *
     * @return array<int, string|null>
     */
    private function removeBom(array $row): array
    {
        if (
            isset($row[0])
            && str_starts_with($row[0], "\xEF\xBB\xBF")
        ) {
            $row[0] = substr($row[0], 3);
        }

        return $row;
    }

    /**
     * Converts selected CSV columns to a SQL INSERT file.
     *
     * @param array<string, string> $fields CSV columns mapped to SQL columns.
     */
    public function convert(
        string $outputFile,
        string $table,
        array $fields,
    ): void {
        $this->validateConversionParameters($table, $fields);

        $writer = new SplFileObject($outputFile, 'wb');

        $header = null;
        $fieldIndexes = [];
        $isFirstValueRow = true;

        foreach ($this->read() as $row) {
            if ($header === null) {
                $header = $row;
                $fieldIndexes = $this->mapFields($header, $fields);

                continue;
            }

            $values = $this->convertRow($row, $fieldIndexes);

            if ($isFirstValueRow) {
                $columns = implode(
                    ', ',
                    array_map(
                        static fn (string $column): string => "`$column`",
                        array_values($fields),
                    ),
                );

                $writer->fwrite("INSERT INTO `$table` ($columns) VALUES" . PHP_EOL);

                $isFirstValueRow = false;
            } else {
                $writer->fwrite(',' . PHP_EOL);
            }

            $writer->fwrite('(' . implode(', ', $values) . ')',);
        }

        if ($isFirstValueRow) {
            throw new FileException('CSV file does not contain any data rows.');
        }

        $writer->fwrite(';' . PHP_EOL);
    }

    /**
     * @param array<string, string> $fields
     */
    private function validateConversionParameters(
        string $table,
        array $fields,
    ): void {
        if ($fields === []) {
            throw new InvalidArgumentException('Fields must not be empty.');
        }

        $this->validateIdentifier($table);

        foreach ($fields as $csvField => $sqlField) {
            if (!is_string($csvField) || !is_string($sqlField)) {
                throw new InvalidArgumentException('CSV and SQL field names must be strings.');
            }

            $this->validateIdentifier($sqlField);
        }
    }

    /**
     * Ensures a table or column name is a safe SQL identifier.
     */
    private function validateIdentifier(string $identifier): void
    {
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier) !== 1) {
            throw new InvalidArgumentException("Invalid SQL identifier \"$identifier\".");
        }
    }

    /**
     * Resolves requested CSV columns to their numeric positions.
     *
     * @param array<int, string|null> $header
     * @param array<string, string> $fields
     *
     * @return list<int>
     */
    private function mapFields(
        array $header,
        array $fields,
    ): array {
        $indexes = [];

        foreach (array_keys($fields) as $csvField) {
            $index = array_search($csvField, $header, true);

            if ($index === false) {
                throw new FileException("CSV field \"$csvField\" not found.");
            }

            $indexes[] = $index;
        }

        return $indexes;
    }

    /**
     * Converts selected row values to SQL literals.
     *
     * @param array<int, string|null> $row
     * @param list<int> $fieldIndexes
     *
     * @return list<string>
     */
    private function convertRow(
        array $row,
        array $fieldIndexes,
    ): array {
        $result = [];

        foreach ($fieldIndexes as $index) {
            if (!array_key_exists($index, $row)) {
                throw new FileException('CSV row contains fewer fields than expected.');
            }

            $result[] = $this->quoteValue($row[$index]);
        }

        return $result;
    }

    /**
     * Converts a CSV value to a SQL literal.
     */
    private function quoteValue(?string $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        $escapedValue = str_replace(
            ["\\", "'"],
            ["\\\\", "''"],
            $value,
        );

        return "'$escapedValue'";
    }
}
