<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ResourcePlugin implements PluginInterface
{
    /** @var array */
    protected $fieldNames = [];

    /** @var RepositoryInterface */
    protected $repository;

    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var array */
    protected $data;

    /** @var ResourceInterface[] */
    protected $resources;

    /** @var string */
    protected $locale;

    public function __construct(
        RepositoryInterface $repository,
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $entityManager
    ) {
        $this->repository = $repository;
        $this->propertyAccessor = $propertyAccessor;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(string $id, string $locale, array $keysToExport): ?array
    {
        //dump($this->data);
        if (!isset($this->data[$id])) {
            return [];
        }

        $result = [];

        foreach ($keysToExport as $exportKey) {
            if ($this->hasPluginDataForExportKey($id, $locale, $exportKey)) {
                $result[$exportKey] = $this->getDataForExportKey($id, $locale, $exportKey);
            } else {
                $result[$exportKey] = '';
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport, string $locale): void
    {
        $this->resources = $this->findResources($idsToExport);
        $this->locale = $locale;
    }

    public function getDataForResources(): void {
        /** @var ResourceInterface $resource */
        foreach ($this->resources as $resource) {
            $this->getDataForSingleResource($resource);
        }
    }

    protected function getDataForSingleResource(ResourceInterface $resource): void {
        $this->addDataForId($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldNames(): array
    {
        return $this->fieldNames;
    }

    protected function hasPluginDataForExportKey(string $id, string $locale, string $exportKey): bool
    {
        return isset($this->data[$id][$locale][$exportKey]);
    }

    protected function getDataForResourceAndExportKey(ResourceInterface $resource, string $locale, string $exportKey)
    {
        return $this->getDataForExportKey((string) $resource->getId(), $locale, $exportKey);
    }

    protected function getDataForExportKey(string $id, string $locale, string $exportKey)
    {
        return $this->data[$id][$locale][$exportKey];
    }

    protected function addDataForResource(ResourceInterface $resource, string $field, $value): void
    {
        $this->data[$resource->getId()][$this->locale][$field] = $value;
    }

    private function addDataForId(ResourceInterface $resource): void
    {
        $fields = $this->entityManager->getClassMetadata(\get_class($resource));
        foreach ($fields->getFieldNames() as $index => $field) {
            $this->fieldNames[$index] = ucfirst($field);

            if (!$this->propertyAccessor->isReadable($resource, $field)) {
                continue;
            }

            $this->addDataForResource(
                $resource,
                ucfirst($field),
                $this->propertyAccessor->getValue($resource, $field)
            );
        }
    }

    /**
     * @param int[] $idsToExport
     *
     * @return ResourceInterface[]
     */
    protected function findResources(array $idsToExport): array
    {
        /** @var ResourceInterface[] $items */
        $items = $this->repository->findBy(['id' => $idsToExport]);

        return $items;
    }
}
