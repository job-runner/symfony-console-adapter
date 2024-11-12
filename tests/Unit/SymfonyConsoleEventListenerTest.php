<?php

declare(strict_types=1);

namespace JobRunner\JobRunner\SymfonyConsole\Tests\Unit;

use DateTimeImmutable;
use JobRunner\JobRunner\Job\Job;
use JobRunner\JobRunner\SymfonyConsole\SymfonyConsoleEventListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

use function array_key_exists;

#[CoversClass(SymfonyConsoleEventListener::class)]
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
        $nextHour             = (new DateTimeImmutable())->setTime((int) (new DateTimeImmutable())->modify('+1 hour')->format('H'), 0, 0);

        $table->expects($this->exactly(5))->method('render');
        $job->expects($this->any())->method('getName')->willReturn('myName');
        $job->expects($this->any())->method('getCronExpression')->willReturn('0 * * * *');
        $consoleSectionOutput->expects($this->exactly(5))->method('clear');
        $table->expects($this->once())->method('setHeaders')->with(['Job name', 'cron expression', 'next run date', 'state', 'output']);
        $table->expects($this->exactly(5))->method('setRows')->with($this->callback(function (mixed $param) use ($nextHour) {
            return $param === match ($this->getNextIncrement('setRows')) {
                1 => [['myName', '0 * * * *', $nextHour->format('Y-m-d H:i:s'), 'start', null]],
                2 => [['myName', '0 * * * *', $nextHour->format('Y-m-d H:i:s'), 'fail', 'toto']],
                3 => [['myName', '0 * * * *', $nextHour->format('Y-m-d H:i:s'), 'notDue', null]],
                4 => [['myName', '0 * * * *', $nextHour->format('Y-m-d H:i:s'), 'isLocked', null]],
                5 => [['myName', '0 * * * *', $nextHour->format('Y-m-d H:i:s'), 'success', 'toto']],
                default => throw new RuntimeException()
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
