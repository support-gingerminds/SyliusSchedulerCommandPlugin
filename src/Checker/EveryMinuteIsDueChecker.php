<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Checker;

use Cron\CronExpression;
use Synolia\SyliusSchedulerCommandPlugin\Components\Exceptions\Checker\IsNotDueException;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Enum\ScheduledCommandStateEnum;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;

/**
 * This checker only works if current date/time is checked every minutes
 */
class EveryMinuteIsDueChecker implements IsDueCheckerInterface
{
    public static function getDefaultPriority(): int
    {
        return 0;
    }

    public function __construct(
        private ScheduledCommandRepositoryInterface $scheduledCommandRepository,
    ) {
    }

    /**
     * @throws \Synolia\SyliusSchedulerCommandPlugin\Components\Exceptions\Checker\IsNotDueException
     */
    public function isDue(CommandInterface $command, ?\DateTimeInterface $dateTime = null): bool
    {
        if (null === $dateTime) {
            $dateTime = new \DateTime();
        }

        $lastCreatedScheduledCommand = $this->scheduledCommandRepository->findLastCreatedCommand($command);
        if ($lastCreatedScheduledCommand !== null && $lastCreatedScheduledCommand->getState() === ScheduledCommandStateEnum::IN_PROGRESS) {
            throw new IsNotDueException();
        }

        $cron = new CronExpression($command->getCronExpression());

        if (!$cron->isDue($dateTime)) {
            throw new IsNotDueException();
        }

        return true;
    }
}
