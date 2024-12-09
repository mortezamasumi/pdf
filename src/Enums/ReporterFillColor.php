<?php

namespace Mortezamasumi\PdfReport\Enums;

enum ReporterFillColor: string
{
    case WHITE     = '255,255,255';
    case LIGHTGRAY = '210,210,210';
    case GRAY      = '180,180,180';
    case DARKGRAY  = '150,150,150';
    case RED       = '150,10,10';
}
