<?php

namespace Mortezamasumi\PdfReport;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Tests\Services\ActionPage;
use TCPDF_FONTS;

class PdfReportServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->registerFacades();
        $this->registerResources();
        $this->registerTCPDFFonts();
    }

    protected function registerFacades()
    {
        $this->app->singleton('PdfReport', function ($app) {
            return new \Mortezamasumi\PdfReport\PdfReport($app);
        });
    }

    protected function registerResources(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'pdf-report');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'pdf-report');
    }

    protected function registerTCPDFFonts(): void
    {
        if (app()->environment('testing')) {
            $src_dir = dirname(__DIR__) . '/resources/fonts/';
            $dst_dir = dirname(__DIR__) . '/vendor/tecnickcom/tcpdf/fonts/';
        } else {
            $src_dir = base_path('/vendor/mortezamasumi/pdf-report/resources/fonts/');
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
