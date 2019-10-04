<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler\DateTimeToStringHandler;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\HandlerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Pool;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Webmozart\Assert\Assert;

class DateTimeToStringHandlerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(DateTimeToStringHandler::class);
    }

    public function it_extends()
    {
        $this->shouldHaveType(Handler::class);
    }

    public function it_should_implement()
    {
        $this->shouldImplement(HandlerInterface::class);
    }

    public function it_should_process_directly()
    {
        $date = \DateTime::createFromFormat('Y-m-d', '2018-01-01');
        $this->handle('test', $date)->shouldBeString();
        $this->handle('test', $date)->shouldBe('2018-01-01');
    }

    public function it_should_process_via_pool()
    {
        $date = \DateTime::createFromFormat('Y-m-d', '2018-01-01');

        $generator = new RewindableGenerator(function () {
            return [$this->getWrappedObject()];
        }, $count = 1);

        $pool = new Pool($generator);

        $result = $pool->handle('test', $date);

        Assert::same('2018-01-01', $result);
    }
}
