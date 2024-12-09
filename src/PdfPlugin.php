<?php

namespace Mortezamasumi\Pdf;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Mortezamasumi\Pdf\Pages\ReportPage;

class PdfPlugin implements Plugin
{
    public function getId(): string
    {
        return 'pdf';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages([
                ReportPage::class,
            ]);
    }

    public function boot(Panel $panel): void {}

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
