<?php
namespace Partitech\SonataExtra\Serializer;

class CircularReferenceHandler
{
    public function __invoke($object)
    {
        return method_exists($object, 'getId') ? $object->getId() : null;
    }
}
