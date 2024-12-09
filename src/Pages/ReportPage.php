<?php

namespace Mortezamasumi\Pdf\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Request;

class ReportPage extends Page
{
    protected static string $view = 'pdf::report-page';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string|Htmlable
    {
        return Request::input('title') ?? __('pdf::pdf.report');
    }
}
