<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

interface ResourceExporterInterface
{
    /**
     * @param int[] $idsToExport
     */
    public function export(array $idsToExport): void;

    /**
     * @param int[] $idsToExport
     * @param string $locale
     *
     * @return array[]
     */
    public function exportData(array $idsToExport, string $locale): array;

    public function setLocales(array $locales): void;

    public function setExportFile(string $filename): void;

    public function getExportedData(): string;

    public function finish(): void;
}
