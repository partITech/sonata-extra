<?php
namespace Partitech\SonataExtra\EventSubscriber;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\Metadata\StaticPropertyMetadata;

class SerializerSubscriber implements EventSubscriberInterface
{
    private array $visited = [];
    public static function getSubscribedEvents(): array
    {
        return [
            [
                'event' => Events::PRE_SERIALIZE,
                'method' => 'onPreSerialize',
                'format' => 'json',
            ],
        ];
    }

    public function onPreSerialize(ObjectEvent $event): void
    {
        $object = $event->getObject();

        if (!method_exists($object, 'getId')) {
            return;
        }

        if (isset($this->visited[spl_object_hash($object)])) {
            $event->getVisitor()->visitProperty(
                new StaticPropertyMetadata('', 'id', $object->getId()),
                $object->getId()
            );

            $event->stopPropagation();
        } else {
            $this->visited[spl_object_hash($object)] = true;
        }
    }
}
