<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Service;

use Sylius\Component\Registry\ServiceRegistry;

abstract class ExchangerRegistry extends ServiceRegistry
{
    public static function buildServiceName(string $type, string $format): string
    {
        return sprintf('%s.%s', $type, $format);
    }

    public abstract function getServicePrefix(): string;
}