<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\PacienteRegister;
use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class PacientePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('paciente')
            ->path('paciente')
            ->login()
            ->registration(PacienteRegister::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Paciente/Resources'), for: 'App\\Filament\\Paciente\\Resources')
            ->discoverPages(in: app_path('Filament/Paciente/Pages'), for: 'App\\Filament\\Paciente\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Paciente/Widgets'), for: 'App\\Filament\\Paciente\\Widgets')
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
            ->plugin(
                FilamentFullCalendarPlugin::make()
                    ->config([
                        'editable' => false, // No permite el drag and drop
                        'selectable' => true,
                        // 'validRange' => [
                        //     'start' => date('Y-m-d'), // A partir de hoy
                        // ],
                        'headerToolbar' => [
                            'left' => 'prev,next today',
                            'center' => 'title',
                            // Aquí se agregan los botones, si se le da espacio lo entenderá como un bloque distinto de botón
                            // Por defecto vienen dayGridMonth, dayGridWeek y 
                            // Los otros útiles pueden ser timeGridWeek, timeGridDay y listDay
                            'right' => 'dayGridMonth,timeGridWeek,dayGridDay listWeek',
                        ],
                        'views' => [
                            'listWeek' => [
                                'buttonText' => 'Agenda Semanal',
                                // 'duration' => ['days' => 3], // Limita la vista de la lista semanal a 4 días
                            ],
                            'listDay' => [
                                'buttonText' => 'Lista Diaria',
                            ],
                        ],
                        'slotMinTime' => '08:00:00', // Hora mínima (8 a.m.)
                        'slotMaxTime' => '19:00:00', // Hora máxima (8 p.m.)
                        'slotDuration' => '00:30:00', // Intervalo de 1 hora entre las horas
                        // Otras configuraciones...
                    ])
            )
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            // Personalización
            ->brandLogo(asset('imagenes/logo-dual.png'))
            ->favicon(asset('imagenes/logo-dual.png'))
            ->brandLogoHeight(fn () => auth()->check() ? '2rem' : '3rem')
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('paciente'); // En config/auth.php está definido paciente para los pacientes
    }
}
