<?php

namespace App\Traits;

trait Enumable
{
    public static function values() {
        $values = [];

        foreach (self::cases() as $case) {
            $values[] = $case->value;
        }

        return $values;
    }

    public static function fromName($name) {
        return constant('self::'.$name);
    }
}
