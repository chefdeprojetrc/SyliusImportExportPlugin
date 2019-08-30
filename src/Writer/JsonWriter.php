<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Writer;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\ExporterException;
use Port\Csv\CsvWriter as PortCsvWriter;

final class JsonWriter implements WriterInterface
{
    private $filename;

    /**
     * {@inheritdoc}
     */
    public function write(array $data): void
    {
        file_put_contents($this->filename, json_encode($data));
    }

    public function setFile(string $filename): void
    {
        $file = fopen($filename, 'w+');
        if (!$file) {
            throw new \Exception('File open failed.');
        }

        $this->filename = $filename;
    }

    public function getFileContent(): string
    {
        return file_get_contents($this->filename);
    }

    public function finish(): void
    {

    }
}
