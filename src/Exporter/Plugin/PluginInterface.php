<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

interface PluginInterface
{
    /**
     * @param mixed[] $resourceFields
     *
     * @return mixed[]
     */
    public function getData(string $id, string $locale, array $resourceFields): array;

    /**
     * @param int[] $idsToExport
     * @param string $locale
     */
    public function init(array $idsToExport, string $locale): void;

    /**
     * @return string[]
     */
    public function getFieldNames(): array;
}
