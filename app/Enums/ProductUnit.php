<?php

namespace App\Enums;

enum ProductUnit: string
{
    case PIECE = 'piece';
    case KILOGRAM = 'kg';
    case GRAM = 'g';
    case LITER = 'liter';
    case MILLILITER = 'ml';
    case PACK = 'pack';

    public function label(): string
    {
        return match($this) {
            self::PIECE => 'Piece',
            self::KILOGRAM => 'Kilogram',
            self::GRAM => 'Gram',
            self::LITER => 'Liter',
            self::MILLILITER => 'Milliliter',
            self::PACK => 'Pack',
        };
    }
}
