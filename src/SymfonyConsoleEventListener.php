<?php

declare(strict_types=1);

namespace JobRunner\JobRunner\SymfonyConsole;

use Cron\CronExpression;
use JobRunner\JobRunner\Event\JobEvent;
use JobRunner\JobRunner\Event\JobFailEvent;
use JobRunner\JobRunner\Event\JobIsLockedEvent;
use JobRunner\JobRunner\Event\JobNotDueEvent;
use JobRunner\JobRunner\Event\JobStartEvent;
use JobRunner\JobRunner\Event\JobSuccessEvent;
use JobRunner\JobRunner\Job\Job;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

use function array_values;

final class SymfonyConsoleEventListener implements JobEvent, JobSuccessEvent, JobFailEvent, JobStartEvent, JobNotDueEvent, JobIsLockedEvent
{
    /** @var array<string, array<array-key, string|null>> */
    private array $rows = [];

    public function __construct(
        private readonly ConsoleSectionOutput $tableSection,
        private readonly Table $table,
    ) {
        $this->table->setHeaders(['Job name', 'cron expression', 'next run date', 'state', 'output']);
    }

    public function start(Job $job): void
    {
        $this->doIt($job, 'start');
    }

    public function fail(Job $job, string $output): void
    {
        $this->doIt($job, 'fail', $output);
    }

    public function success(Job $job, string $output): void
    {
        $this->doIt($job, 'success', $output);
    }

    public function notDue(Job $job): void
    {
        $this->doIt($job, 'notDue');
    }

    public function isLocked(Job $job): void
    {
        $this->doIt($job, 'isLocked');
    }

    private function doIt(Job $job, string $state, string|null $output = null): void
    {
        $this->rows[$job->getName()] = [
            $job->getName(),
            $job->getCronExpression(),
            (new CronExpression($job->getCronExpression()))->getNextRunDate()->format('Y-m-d H:i:s'),
            $state,
            $output,
        ];
        $this->tableSection->clear();
        $this->table->setRows(array_values($this->rows));
        $this->table->render();
    }
}
