<?php

namespace Tests\Services;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Tests\Actions\Fixtures\Pages\Actions;
use Filament\Tests\Panels\Fixtures\Pages\Settings;
use Filament\Tests\Panels\Fixtures\Resources\PostCategoryResource;
use Filament\Tests\Panels\Fixtures\Resources\PostResource;
use Filament\Tests\Panels\Fixtures\Resources\ProductResource;
use Filament\Tests\Panels\Fixtures\Resources\UserResource;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Mortezamasumi\PdfReport\Pages\ReportPage;
use Mortezamasumi\PdfReport\PdfReportPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
                   ->default()
                   ->id('admin')
                   ->login()
                   ->registration()
                   ->passwordReset()
                   ->emailVerification()
                   //    ->pages([
                   //        ReportPage::class,
                   //    ])
                   ->resources([
                       //    PostResource::class,
                       //    PostCategoryResource::class,
                       //    ProductResource::class,
                       //    UserResource::class,
                   ])
                   ->pages([
                       //    Pages\Dashboard::class,
                       //    Actions::class,
                       //    Settings::class,
                   ])
                   ->middleware([
                       EncryptCookies::class,
                       AddQueuedCookiesToResponse::class,
                       StartSession::class,
                       AuthenticateSession::class,
                       ShareErrorsFromSession::class,
                       VerifyCsrfToken::class,
                       SubstituteBindings::class,
                       DisableBladeIconComponents::class,
                       DispatchServingFilamentEvent::class,
                   ])
                   ->authMiddleware([
                       Authenticate::class,
                   ])
                   ->plugins([
                       PdfReportPlugin::make(),
                   ]);
    }
}