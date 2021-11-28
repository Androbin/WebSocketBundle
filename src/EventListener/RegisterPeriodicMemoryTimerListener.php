<?php declare(strict_types=1);

namespace Gos\Bundle\WebSocketBundle\EventListener;

use Gos\Bundle\WebSocketBundle\Event\ServerLaunchedEvent;
use Gos\Bundle\WebSocketBundle\Periodic\PeriodicMemoryUsage;
use Gos\Bundle\WebSocketBundle\Server\App\Registry\PeriodicRegistry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * @internal
 */
final class RegisterPeriodicMemoryTimerListener implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private PeriodicRegistry $periodicRegistry)
    {
    }

    public function __invoke(ServerLaunchedEvent $event): void
    {
        if (!$event->isProfiling()) {
            return;
        }

        $memoryUsagePeriodicTimer = new PeriodicMemoryUsage();

        if (null !== $this->logger) {
            $memoryUsagePeriodicTimer->setLogger($this->logger);
        }

        $this->periodicRegistry->addPeriodic($memoryUsagePeriodicTimer);
    }
}
