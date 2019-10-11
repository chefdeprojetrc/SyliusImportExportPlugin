<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Command;

use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Service\ExchangerRegistry;
use Sylius\Component\Registry\ServiceRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractDataCommand extends Command
{
    /** @var InputInterface */
    protected $input;
    /** @var OutputInterface */
    protected $output;
    /** @var ExchangerRegistry */
    protected $registry;

    public function __construct(ExchangerRegistry $registry)
    {
        parent::__construct();

        $this->registry = $registry;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->input = $input;
        $this->output = $output;
    }

    protected function checkAvailableExchanger(string $exchangerType): ?string
    {
        $error = null;

        $exchangerName = $this->input->getArgument($exchangerType);
        $format = $this->input->getOption('format');

        if(empty($exchangerName)) {
            $error = sprintf('There is no specified %s.', $exchangerType);
        }elseif(null === $format) {
            $error = sprintf('There is no specified format for %s.', $exchangerType);
        }else{
            $exchangerName = $this->registry->getServicePrefix().$exchangerName;
            $serviceName = $this->registry::buildServiceName($exchangerName, $format);

            if (!$this->registry->has($serviceName)) {
                $error = sprintf('There is no specified \'%s\' %s.', $serviceName, $exchangerType);
            }
        }

        if (!is_null($error)) {
            $this->displayAvailableExchangers($exchangerType, $error);
            return null;
        }

        return $serviceName;
    }

    protected function displayAvailableExchangers(string $type, ?string $errorMessage = null): void
    {
        $this->output->writeln("<info>Available ${type}s:</info>");
        $all = array_keys($this->registry->all());

        $exchangers = [];
        foreach ($all as $exchanger) {
            $exchanger = explode('.', $exchanger);
            $dotCount = count($exchanger);

            if($dotCount > 1) {
                $exchangers[$exchanger[$dotCount-2]][] = $exchanger[$dotCount-1];
            }
        }

        $list = [];
        foreach ($exchangers as $exchanger => $formats) {
            $list[] = sprintf(
                '%s (formats: %s)',
                $exchanger,
                implode(', ', $formats)
            );
        }

        $io = new SymfonyStyle($this->input, $this->output);
        $io->listing($list);

        if ($errorMessage) {
            throw new \RuntimeException($errorMessage);
        }
    }
}
