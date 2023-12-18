<?php

namespace Partitech\SonataExtra\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AsAdmin
{
    public array $values;

    public function __construct(mixed ...$values)
    {
        $this->values = $values;
    }

    public function getTags(): ?array
    {
        $tags = $this->values;
        unset($tags['calls']);

        return $tags;
    }

    public function getCalls(): array
    {
        return $this->values['calls'] ?? [];
    }
}
