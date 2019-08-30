<?php

/*
 * This is file is required as long as SyliusImportExportPlugin supports Symfony versions lower than 4.1. Before this version, ParameterBag wasn't a service available.
 *
 * See: https://symfony.com/blog/new-in-symfony-4-1-getting-container-parameters-as-a-service
 */

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection\ParameterBag;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class ParameterBag extends FrozenParameterBag implements ParameterBagInterface
{
    private $container;

    public function __construct(Container $container)
    {
        parent::__construct();

        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->container->getParameterBag()->all();
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return $this->container->hasParameter($name);
    }
}
