<?php
namespace Partitech\SonataExtra\EventSubscriber;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\Events;

class SerializerSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::PRE_SERIALIZE,
                'method' => 'onPreSerialize',
                'format' => 'json',
            ],
        ];
    }

    public function onPreSerialize(ObjectEvent $event)
    {
        $object = $event->getObject();
        $type = get_class($object);

        // Ajoutez une condition pour vérifier si l'objet est une instance de la classe qui a une méthode getId
        // Remplacez "VotreEntite" par le nom de votre classe d'entité ou ajoutez d'autres conditions selon le cas
        if (!method_exists($object, 'getId')) {
            // Si l'objet n'a pas de méthode getId(), juste retourner sans modification
            // Cela inclut les instances de DateTime ou d'autres classes non entité
            return;
        }

        // Vérifier si l'objet a déjà été visité
        if (isset($this->visited[spl_object_hash($object)])) {
            // Modifier la représentation de l'objet pour n'inclure que son ID
            $event->getVisitor()->visitProperty(
                new \JMS\Serializer\Metadata\StaticPropertyMetadata('', 'id', $object->getId()),
                $object->getId()
            );

            // Stopper la sérialisation de cet objet pour éviter la référence circulaire
            $event->stopPropagation();
        } else {
            // Marquer l'objet comme visité
            $this->visited[spl_object_hash($object)] = true;
        }
    }
}
