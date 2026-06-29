<?php

declare(strict_types=1);

namespace App\Enums;

enum BrazilStateEnum: int
{
    public function label(): string
    {
        return match ($this) {
            self::AC => 'Acre', self::AL => 'Alagoas', self::AP => 'Amapá',
            self::AM => 'Amazonas', self::BA => 'Bahia', self::CE => 'Ceará',
            self::DF => 'Distrito Federal', self::ES => 'Espírito Santo',
            self::GO => 'Goiás', self::MA => 'Maranhão', self::MT => 'Mato Grosso',
            self::MS => 'Mato Grosso do Sul', self::MG => 'Minas Gerais',
            self::PA => 'Pará', self::PB => 'Paraíba', self::PR => 'Paraná',
            self::PE => 'Pernambuco', self::PI => 'Piauí', self::RJ => 'Rio de Janeiro',
            self::RN => 'Rio Grande do Norte', self::RS => 'Rio Grande do Sul',
            self::RO => 'Rondônia', self::RR => 'Roraima', self::SC => 'Santa Catarina',
            self::SP => 'São Paulo', self::SE => 'Sergipe', self::TO => 'Tocantins',
        };
    }

    /**
     * Gera o formato exato esperado pela API do IBGE para o Mock
     */
    public static function forIbgeMock(): array
    {
        return collect(self::cases())->map(fn ($state) => [
            'id' => $state->value,
            'sigla' => $state->name,
            'nome' => $state->label(),
        ])->all();
    }
    case AC = 12;
    case AL = 27;
    case AP = 16;
    case AM = 13;
    case BA = 29;
    case CE = 23;
    case DF = 53;
    case ES = 32;
    case GO = 52;
    case MA = 21;
    case MT = 51;
    case MS = 50;
    case MG = 31;
    case PA = 15;
    case PB = 25;
    case PR = 41;
    case PE = 26;
    case PI = 22;
    case RJ = 33;
    case RN = 24;
    case RS = 43;
    case RO = 11;
    case RR = 14;
    case SC = 42;
    case SP = 35;
    case SE = 28;
    case TO = 17;
}
