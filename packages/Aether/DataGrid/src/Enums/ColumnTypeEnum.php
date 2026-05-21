<?php

namespace Aether\DataGrid\Enums;

use Aether\DataGrid\ColumnTypes\Aggregate;
use Aether\DataGrid\ColumnTypes\Boolean;
use Aether\DataGrid\ColumnTypes\Date;
use Aether\DataGrid\ColumnTypes\Datetime;
use Aether\DataGrid\ColumnTypes\Decimal;
use Aether\DataGrid\ColumnTypes\Integer;
use Aether\DataGrid\ColumnTypes\Text;
use Aether\DataGrid\Exceptions\InvalidColumnTypeException;

enum ColumnTypeEnum: string
{
    /**
     * Cadena.
     */
    case STRING = 'string';

    /**
     * Entero.
     */
    case INTEGER = 'integer';

    /**
     * Flotar.
     */
    case FLOAT = 'float';

    /**
     * Booleano.
     */
    case BOOLEAN = 'boolean';

    /**
     * Fecha.
     */
    case DATE = 'date';

    /**
     * Hora de la fecha.
     */
    case DATETIME = 'datetime';

    /**
     * Agregar.
     */
    case AGGREGATE = 'aggregate';

    /**
     * Obtenga el nombre de clase correspondiente para el tipo de columna.
     */
    public static function getClassName(string $type): string
    {
        return match ($type) {
            self::STRING->value => Text::class,
            self::INTEGER->value => Integer::class,
            self::FLOAT->value => Decimal::class,
            self::BOOLEAN->value => Boolean::class,
            self::DATE->value => Date::class,
            self::DATETIME->value => Datetime::class,
            self::AGGREGATE->value => Aggregate::class,
            default => throw new InvalidColumnTypeException("Invalid column type: {$type}"),
        };
    }
}
