<?php

namespace Mortezamasumi\PdfReport\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Request;

class ReportPage extends Page
{
    protected static string $view = 'pdf-report::report-page';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string|Htmlable
    {
        return Request::input('title') ?? __('pdf-report::pdf-report.report');
    }
}
