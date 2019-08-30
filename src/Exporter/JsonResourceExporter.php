<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\TransformerPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\WriterInterface;

final class JsonResourceExporter extends ResourceExporter
{
    private $data = [];

    /** @var string */
    private $filename;

    /**
     * @param string[] $resourceKeys
     */
    public function __construct(
        WriterInterface $writer,
        PluginPoolInterface $pluginPool,
        array $resourceKeys,
        ?TransformerPoolInterface $transformerPool
    ) {
        parent::__construct($writer, $pluginPool, $resourceKeys, $transformerPool);
    }

    /**
     * {@inheritdoc}
     */
    public function export(array $idsToExport): void
    {
        // @todo manage default locale on resource export
        $locale = 'fr_FR';

        $this->pluginPool->initPlugins($idsToExport, $locale);

        foreach ($idsToExport as $id) {
            $this->data[] = $this->getDataForId((string) $id, $locale);
        }

        if ($this->filename !== null) { // only true if command is used to export
            $this->writer->setFile($this->filename);
            $this->writer->write($this->data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExportedData(): string
    {
        return json_encode($this->data) ?: '';
    }

    /**
     * {@inheritdoc}
     */
    public function setExportFile(string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function finish(): void
    {
        // no finish needed
    }
}
