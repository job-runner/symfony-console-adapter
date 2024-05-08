# symfony/console for JobRunner

[![Build Status](https://github.com/job-runner/symfony-console-adapter/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/job-runner/symfony-console-adapter/actions/workflows/continuous-integration.yml)
[![Type Coverage](https://shepherd.dev/github/job-runner/symfony-console-adapter/coverage.svg)](https://shepherd.dev/github/job-runner/symfony-console-adapter)
[![Type Coverage](https://shepherd.dev/github/job-runner/symfony-console-adapter/level.svg)](https://shepherd.dev/github/job-runner/symfony-console-adapter)
[![Latest Stable Version](https://poser.pugx.org/job-runner/symfony-console-adapter/v/stable)](https://packagist.org/packages/job-runner/symfony-console-adapter)
[![License](https://poser.pugx.org/job-runner/symfony-console-adapter/license)](https://packagist.org/packages/job-runner/symfony-console-adapter)

This package provides a symfony/console adapter for JobRunner.

## Installation

```bash
composer require job-runner/symfony-console-adapter
```

## Usage

````php
<?php

declare(strict_types=1);

use JobRunner\JobRunner\Job\CliJob;
use JobRunner\JobRunner\Job\JobList;
use JobRunner\JobRunner\CronJobRunner;
use JobRunner\JobRunner\SymfonyConsole\SymfonyConsoleEventListener;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

require 'vendor/autoload.php';

(new SingleCommandApplication())
    ->setName('My Super Command') // Optional
    ->setVersion('1.0.0') // Optional
    ->addOption('bar', null, InputOption::VALUE_REQUIRED)
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $jobCollection = new JobList();
        $jobCollection->push(new CliJob('php ' . __DIR__ . '/tutu.php', '* * * * *'));
        $jobCollection->push(new CliJob('php ' . __DIR__ . '/titi.php', '* * * * *', 'sample'));
        $jobCollection->push(new CliJob('php ' . __DIR__ . '/titi.php', '1 1 1 1 1', 'hehe'));
        $jobCollection->push(new CliJob('php ' . __DIR__ . '/arg.php', '* * * * *'));

        $section = $output->section();

        CronJobRunner::create()
            ->withEventListener(new SymfonyConsoleEventListener($section, new Table($section)))
            ->run($jobCollection);

    })
    ->run();
````
