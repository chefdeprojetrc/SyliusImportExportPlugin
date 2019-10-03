<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Command;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ExporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ResourceExporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\Mapper\ResourceMapperService;
use Sylius\Component\Locale\Provider\LocaleProviderInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class ExportDataCommand extends AbstractDataCommand
{
    use ContainerAwareTrait;

    /** @var ResourceMapperService  */
    private $mapperService;
    /** @var LocaleProviderInterface  */
    private $localeProvider;

    public function __construct(
        ExporterRegistry $exporterRegistry,
        ResourceMapperService $mapperService,
        LocaleProviderInterface $localeProvider
    )
    {
        $this->mapperService = $mapperService;
        $this->localeProvider = $localeProvider;

        parent::__construct($exporterRegistry);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('sylius:export')
            ->setDescription('Export data to a file.')
            ->setDefinition([
                new InputArgument('exporter', InputArgument::OPTIONAL, 'The exporter to use.'),
                new InputArgument('file', InputArgument::OPTIONAL, 'The target file to export to.'),
                new InputOption('format', null, InputOption::VALUE_REQUIRED, 'The format of the file to export to'),
                new InputOption('locales', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The locale used for exporting data'),
                /** @todo Extracting details to show with this option. At the moment it will have no effect */
                new InputOption('details', null, InputOption::VALUE_NONE,'If to return details about skipped/failed rows'),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        parent::execute($input, $output);

        $name = $this->checkAvailableExchanger('exporter');
        if(null === $name) {
            exit(1);
        }

        $file = $input->getArgument('file');
        $locales = $input->getOption('locales');
        if(empty($locales)) {
            $locales = [
                'de_DE',
                'en_GB',
                'en_US',
                'es_ES',
                'fr_CH',
                'fr_FR',
                'it_IT',
                'pt_BR',
                'ru_RU'
            ];
        }

        /** TODO: Refacto repository management */
        /** @var RepositoryInterface $repository */
        $repository = $this->container->get('sylius.repository.' . 'product_variant');
        $items = $repository->findAll();

        $idsToExport = array_map(function (ResourceInterface $item) {
            return $item->getId();
        }, $items);

        /** @var ResourceExporterInterface $service */
        $service = $this->registry->get($name);
        $service->setExportFile($file);
        $service->setLocales($locales);

        $service->export($idsToExport);

        $service->finish();

        $output->writeln(sprintf(
          "<info>Exported %d item(s) to '%s' via the %s exporter</info>",
          count($items),
          $file,
          $name
        ));
    }


}
