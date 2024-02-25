<?php

namespace App\Enums;

enum CitaEstadoEnum : string {
    case AGENDADO = 'Agendado';
    case RESERVADO = 'Reservado';
    case CONSULTADO = 'Consultado';
    case ABANDONADO = 'Abandonado';
    case CANCELADO = 'Cancelado';

    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getDefault(): string
    {
        return self::AGENDADO->value;
    }
}