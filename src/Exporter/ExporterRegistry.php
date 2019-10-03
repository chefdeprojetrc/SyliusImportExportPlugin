<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use FriendsOfSylius\SyliusImportExportPlugin\Service\ExchangerRegistry;

final class ExporterRegistry extends ExchangerRegistry
{
    public const EVENT_HOOK_NAME_PREFIX_GRID_BUTTONS = 'app.grid_event_listener.admin.crud';

    /**
     * @param string[] $formats
     */
    public static function buildGridButtonsEventHookName(string $type, array $formats): string
    {
        $format = implode('_', $formats);

        return sprintf('%s_%s_%s', self::EVENT_HOOK_NAME_PREFIX_GRID_BUTTONS, $type, $format);
    }

    public function getServicePrefix(): string
    {
        return 'sylius.';
    }
}
