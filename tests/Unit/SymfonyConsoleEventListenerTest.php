<?php

declare(strict_types=1);

namespace JobRunner\JobRunner\SymfonyConsole\Tests\Unit;

use JobRunner\JobRunner\Job\Job;
use JobRunner\JobRunner\SymfonyConsole\SymfonyConsoleEventListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

#[CoversClass(SymfonyConsoleEventListener::class)]
class SymfonyConsoleEventListenerTest extends TestCase
{
    public function testSuccess(): void
    {
        $table                = $this->createMock(Table::class);
        $consoleSectionOutput = $this->createMock(ConsoleSectionOutput::class);
        $job                  = $this->createMock(Job::class);

        $job->expects($this->any())->method('getName')->willReturn('myName');
        $job->expects($this->any())->method('getCronExpression')->willReturn('0 * * * *');
        $consoleSectionOutput->expects($this->exactly(5))->method('clear');
        $table->expects($this->exactly(5))->method('render');
        $table->expects($this->once())->method('setHeaders')->with(['Job name', 'cron expression', 'next run date', 'state', 'output']);
        $table->expects($this->exactly(5))->method('setRows');

        $sUT = new SymfonyConsoleEventListener($consoleSectionOutput, $table);

        $sUT->start($job);
        $sUT->fail($job, 'toto');
        $sUT->notDue($job);
        $sUT->isLocked($job);
        $sUT->success($job, 'toto');
    }
}
