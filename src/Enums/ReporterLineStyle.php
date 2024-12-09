<?php

namespace Mortezamasumi\PdfReport\Enums;

enum ReporterLineStyle: string
{
    case NORMAL_LINE   = 'normal_line';
    case THIN_LINE     = 'thin_line';
    case THICK_LINE    = 'thick_line';
    case NORMAL_DASH1  = 'normal_dash1';
    case THIN_DASH1    = 'thin_dash1';
    case THICK_DASH1   = 'thick_dash1';
    case NORMAL_DASH21 = 'normal_dash21';
    case THIN_DASH21   = 'thin_dash21';
    case THICK_DASH21  = 'thick_dash21';
    case NORMAL_DASH2  = 'normal_dash2';
    case THIN_DASH2    = 'thin_dash2';
    case THICK_DASH2   = 'thick_dash2';
    case NORMAL_DASH3  = 'normal_dash3';
    case THIN_DASH3    = 'thin_dash3';
    case THICK_DASH3   = 'thick_dash3';

    public function getValue(): array
    {
        return match ($this) {
            self::NORMAL_LINE   => ['dash' => '',      'width' => 0],
            self::THIN_LINE     => ['dash' => '',      'width' => 0.01],
            self::THICK_LINE    => ['dash' => '',      'width' => 0.75],
            self::NORMAL_DASH1  => ['dash' => '1',     'width' => 0],
            self::THIN_DASH1    => ['dash' => '1',     'width' => 0.01],
            self::THICK_DASH1   => ['dash' => '1',     'width' => 0.75],
            self::NORMAL_DASH21 => ['dash' => '2,1',   'width' => 0],
            self::THIN_DASH21   => ['dash' => '2,1',   'width' => 0.01],
            self::THICK_DASH21  => ['dash' => '2,1',   'width' => 0.75],
            self::NORMAL_DASH2  => ['dash' => '2',     'width' => 0],
            self::THIN_DASH2    => ['dash' => '2',     'width' => 0.01],
            self::THICK_DASH2   => ['dash' => '2',     'width' => 0.75],
            self::NORMAL_DASH3  => ['dash' => '3,1,1', 'width' => 0],
            self::THIN_DASH3    => ['dash' => '3,1,1', 'width' => 0.01],
            self::THICK_DASH3   => ['dash' => '3,1,1', 'width' => 0.75],
        };
    }
}
