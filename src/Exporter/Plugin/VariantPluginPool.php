<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use FriendsOfSylius\SyliusImportExportPlugin\Service\AttributeCodesProviderInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\ImageTypesProviderInterface;

final class VariantPluginPool extends PluginPool
{
    /** @var ImageTypesProviderInterface */
    private $imageTypesProvider;
    /** @var AttributeCodesProviderInterface */
    private $attributeCodesProvider;

    public function __construct(
        array $plugins,
        array $exportKeys,
        AttributeCodesProviderInterface $attributeCodesProvider,
        ImageTypesProviderInterface $imageTypesProvider
    ) {
        parent::__construct($plugins, $exportKeys);
        $this->attributeCodesProvider = $attributeCodesProvider;
        $this->imageTypesProvider = $imageTypesProvider;
    }

    public function initPlugins(array $ids, string $locale): void
    {
        $this->exportKeysAvailable = $this->exportKeys;
        parent::initPlugins($ids, $locale);
    }
}
