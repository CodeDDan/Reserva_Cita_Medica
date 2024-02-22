<?php

namespace App\Console;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly(); // NOSONAR
        $schedule->call(function () {
            // Código para actualizar el estado de las citas al final del día
            DB::table('citas')
                ->whereDate('fecha_inicio_cita', Carbon::today())
                ->whereNull('fecha_fin_cita')
                ->where('estado', 'Agendado')
                ->update(['estado' => 'Abandonado']);
        })->dailyAt('23:59'); // Se ejecutará justo antes de la medianoche
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require_once base_path('routes/console.php');
    }
}
