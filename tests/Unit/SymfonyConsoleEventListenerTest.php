<?php

declare(strict_types=1);

namespace JobRunner\JobRunner\SymfonyConsole\Tests\Unit;

use JobRunner\JobRunner\Job\Job;
use JobRunner\JobRunner\SymfonyConsole\SymfonyConsoleEventListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

/** @covers \JobRunner\JobRunner\SymfonyConsole\SymfonyConsoleEventListener */
class SymfonyConsoleEventListenerTest extends TestCase
{
    public function testSuccess(): void
    {
        $table                = self::createMock(Table::class);
        $consoleSectionOutput = self::createMock(ConsoleSectionOutput::class);
        $job                  = self::createMock(Job::class);

        $table->expects($this->exactly(5))->method('render');
        $job->expects($this->any())->method('getName')->willReturn('myName');
        $consoleSectionOutput->expects($this->exactly(5))->method('clear');
        $table->expects($this->once())->method('setHeaders')->with(['Job name', 'state', 'output']);
        $table->expects($this->exactly(5))->method('setRows')->withConsecutive(
            [[['myName', 'start', null]]],
            [[['myName', 'fail', 'toto']]],
            [[['myName', 'notDue', null]]],
            [[['myName', 'isLocked', null]]],
            [[['myName', 'success', 'toto']]],
        );

        $sUT = new SymfonyConsoleEventListener($consoleSectionOutput, $table);

        $sUT->start($job);
        $sUT->fail($job, 'toto');
        $sUT->notDue($job);
        $sUT->isLocked($job);
        $sUT->success($job, 'toto');
    }
}
