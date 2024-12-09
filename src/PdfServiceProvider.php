<?php

namespace Mortezamasumi\Pdf;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Mortezamasumi\Pdf\Console\PdfCommand;
use Mortezamasumi\Pdf\Pdf;
use TCPDF_FONTS;

class PdfServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->registerFacades();
        $this->registerCommands();
        $this->registerResources();
        $this->registerTCPDFFonts();
    }

    protected function registerFacades()
    {
        $this->app->singleton('Pdf', fn($app) => new Pdf($app));
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PdfCommand::class,
            ]);
        }
    }

    protected function registerResources(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'pdf');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'pdf');
        $this->loadRoutesFrom(__DIR__ . '/../routes/console.php');
    }

    protected function registerTCPDFFonts(): void
    {
        if (app()->environment('testing')) {
            $src_dir = dirname(__DIR__) . '/resources/fonts/';
            $dst_dir = dirname(__DIR__) . '/vendor/tecnickcom/tcpdf/fonts/';
        } else {
            $src_dir = base_path('/vendor/mortezamasumi/pdf/resources/fonts/');
            $dst_dir = base_path('/vendor/tecnickcom/tcpdf/fonts/');
        }

        if (!File::exists("{$dst_dir}__customfonts")) {
            foreach (File::files("{$src_dir}ttf") as $file) {
                TCPDF_FONTS::addTTFfont($file->getPathname());
            }

            foreach (File::files("{$src_dir}core") as $file) {
                File::copy($file->getPathname(), $dst_dir . $file->getBasename());
            }
        }
    }
}
