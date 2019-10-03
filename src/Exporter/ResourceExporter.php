<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\TransformerPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\WriterInterface;

class ResourceExporter implements ResourceExporterInterface
{
    /** @var string[] */
    protected $locales;

    /** @var string[] */
    protected $resourceKeys;

    /** @var WriterInterface */
    protected $writer;

    /** @var PluginPoolInterface */
    protected $pluginPool;

    /** @var TransformerPoolInterface|null */
    protected $transformerPool;

    /**
     * @param string[] $resourceKeys
     */
    public function __construct(
        WriterInterface $writer,
        PluginPoolInterface $pluginPool,
        array $resourceKeys,
        ?TransformerPoolInterface $transformerPool
    ) {
        $this->writer = $writer;
        $this->pluginPool = $pluginPool;
        $this->transformerPool = $transformerPool;
        $this->resourceKeys = $resourceKeys;
        $this->setLocales(['fr_FR']);
    }

    /**
     * {@inheritdoc}
     */
    public function setExportFile(string $filename): void
    {
        $this->writer->setFile($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function setLocales(array $locales = []): void
    {
        $this->locales = $locales;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportedData(): string
    {
        return $this->writer->getFileContent();
    }

    /**
     * {@inheritdoc}
     */
    public function export(array $idsToExport): void
    {
        $this->writer->write($this->resourceKeys);

        foreach ($this->locales as $locale) {
            $this->pluginPool->initPlugins($idsToExport, $locale);

            foreach ($idsToExport as $id) {
                $this->writeDataForId((string) $id, $locale);
            }
        }
    }

    /**
     * @param int[] $idsToExport
     * @param string $locale
     *
     * @return array[]
     */
    public function exportData(array $idsToExport, string $locale): array
    {
        $this->pluginPool->initPlugins($idsToExport, $locale);
        $this->writer->write($this->resourceKeys);

        $exportIdDataArray = [];
        foreach ($idsToExport as $id) {
            $exportIdDataArray[$locale.'-'.$id] = $this->getDataForId((string) $id, $locale);
        }

        return $exportIdDataArray;
    }

    private function writeDataForId(string $id, string $locale): void
    {
        $dataForId = $this->getDataForId($id, $locale);

        $this->writer->write($dataForId);
    }

    protected function getDataForId(string $id, string $locale): array
    {
        $data = $this->pluginPool->getDataForId($id, $locale);

        if (null !== $this->transformerPool) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->transformerPool->handle($key, $value);
            }
        }

        return $data;
    }

    public function finish(): void
    {
        $this->writer->finish();
    }
}
