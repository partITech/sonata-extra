<?php

namespace Partitech\SonataExtra\Enum;

enum EditorStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case UNPUBLISHED = 'unpublished';

    public static function getValues(): array
    {
        $data = [];

        foreach (self::cases() as $item) {
            $data[$item->value] = $item->getName();
        }

        return $data;
    }

    public static function getChoiceValues(): array
    {
        return array_flip(self::getValues());
    }

    public function getName(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::PUBLISHED => 'Publié',
            self::UNPUBLISHED => 'Dépublié',
        };
    }
}
