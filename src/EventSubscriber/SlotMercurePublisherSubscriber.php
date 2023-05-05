<?php

namespace App\EventSubscriber;

use App\Events\SlotBookedEvent;
use App\Events\SlotCanceledEvent;
use App\Events\SlotEvent;
use App\Merryweather\AppConfig;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mercure\Exception\RuntimeException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class SlotMercurePublisherSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly HubInterface $hub, private readonly AppConfig $appConfig)
    {
    }

    /**
     * @param SlotEvent $event
     * @return void
     */
    public function publish(SlotEvent $event, string $type): void
    {
        if ($this->appConfig->isMercureActive()) {
            $update = new Update(
                'booking',
                json_encode(['event' => $type])
            );
            try {
                $res = $this->hub->publish($update);
                $this->logger->info($res);
            } catch (RuntimeException $runtimeException) {
                $this->logger->error('Mercure: ' . $runtimeException->getMessage());
                $this->logger->error($runtimeException->getPrevious()?->getMessage());
            }
        }
    }

    public function onSlotCanceled(SlotCanceledEvent $event): void
    {
        $this->publish($event, 'canceled');
    }

    public function onSlotBooked(SlotBookedEvent $event): void
    {
        $this->publish($event, 'booked');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SlotCanceledEvent::NAME => 'onSlotCanceled',
            SlotBookedEvent::NAME => 'onSlotBooked',
        ];
    }
}
