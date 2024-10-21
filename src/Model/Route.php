<?php

namespace Partitech\SonataExtra\Model;

class Route
{
    private string $name;
    private string $path;
    private array $methods;

    public function __construct(string $name, string $path, array $methods)
    {
        $this->name = $name;
        $this->path = $path;
        $this->methods = $methods;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getRoles(): array
    {
        $parts = explode('_', $this->name);

        $roles = [];
        foreach ($this->methods as $method) {
            $roles[] = sprintf('ROLE_%s_%s_%s', strtoupper($parts[1]), strtoupper($parts[2]), strtoupper($method));
        }

        return $roles;
    }
}
