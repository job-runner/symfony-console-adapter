<?php

declare(strict_types=1);

namespace JobRunner\JobRunner\SymfonyConsole\Tests\Unit;

use JobRunner\JobRunner\Job\Job;
use JobRunner\JobRunner\SymfonyConsole\SymfonyConsoleEventListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

use function array_key_exists;

/** @covers \JobRunner\JobRunner\SymfonyConsole\SymfonyConsoleEventListener */
class SymfonyConsoleEventListenerTest extends TestCase
{
    /** @var array<string, int> */
    private array $matcher = [];

    private function getNextIncrement(string $name): int
    {
        if (! array_key_exists($name, $this->matcher)) {
            $this->matcher[$name] = 0;
        }

        $this->matcher[$name]++;

        return $this->matcher[$name];
    }

    public function testSuccess(): void
    {
        $table                = self::createMock(Table::class);
        $consoleSectionOutput = self::createMock(ConsoleSectionOutput::class);
        $job                  = self::createMock(Job::class);

        $table->expects($this->exactly(5))->method('render');
        $job->expects($this->any())->method('getName')->willReturn('myName');
        $consoleSectionOutput->expects($this->exactly(5))->method('clear');
        $table->expects($this->once())->method('setHeaders')->with(['Job name', 'state', 'output']);
        $table->expects($this->exactly(5))->method('setRows')->with($this->callback(function (mixed $param) {
            return $param === match ($this->getNextIncrement('setRows')) {
                1 => [['myName', 'start', null]],
                2 => [['myName', 'fail', 'toto']],
                3 => [['myName', 'notDue', null]],
                4 => [['myName', 'isLocked', null]],
                5 => [['myName', 'success', 'toto']],
            };
        }));

        $sUT = new SymfonyConsoleEventListener($consoleSectionOutput, $table);

        $sUT->start($job);
        $sUT->fail($job, 'toto');
        $sUT->notDue($job);
        $sUT->isLocked($job);
        $sUT->success($job, 'toto');
    }
}
