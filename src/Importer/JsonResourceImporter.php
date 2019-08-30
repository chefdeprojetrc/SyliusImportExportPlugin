<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\Common\Persistence\ObjectManager;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use Port\Reader\ReaderFactory;

final class JsonResourceImporter extends ResourceImporter implements SingleDataArrayImporterInterface
{
    public function __construct(
        ReaderFactory $readerFactory,
        ObjectManager $objectManager,
        ResourceProcessorInterface $resourceProcessor,
        ImportResultLoggerInterface $importerResult,
        int $batchSize,
        bool $failOnIncomplete,
        bool $stopOnFailure
    ) {
        parent::__construct(
            $readerFactory,
            $objectManager,
            $resourceProcessor,
            $importerResult,
            $batchSize,
            $failOnIncomplete,
            $stopOnFailure
        );
    }

    /**
     * {@inheritdoc}
     */
    public function import(string $fileName): ImporterResultInterface
    {
        $this->result->start();

        $contents = file_get_contents($fileName);
        if (false === $contents) {
            throw new ImporterException(sprintf('File %s could not be loaded', $fileName));
        }

        foreach (json_decode($contents, true) as $i => $row) {
            if ($this->importData($i, $row)) {
                break;
            }
        }

        if ($this->batchCount) {
            $this->objectManager->flush();
        }

        $this->result->stop();

        return $this->result;
    }

    /**
     * {@inheritdoc}
     */
    public function importSingleDataArrayWithoutResult(array $dataToImport): void
    {
        $this->resourceProcessor->process($dataToImport);
        $this->objectManager->flush();
    }
}
