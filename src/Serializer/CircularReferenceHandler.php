<?php
namespace Partitech\SonataExtra\Serializer;

class CircularReferenceHandler
{
    public function __invoke($object)
    {
        // Retourne l'ID de l'entité, ou null si l'objet n'a pas de méthode getId()
        return method_exists($object, 'getId') ? $object->getId() : null;
    }
}
