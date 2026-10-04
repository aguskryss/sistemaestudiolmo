<?php

use Illuminate\Support\Facades\Schedule;

// En Hostinger: cron cada minuto → php artisan schedule:run
Schedule::command('recordatorios:vencimientos')->dailyAt('07:00')->withoutOverlapping();
Schedule::command('recordatorios:enviar')->everyFiveMinutes()->withoutOverlapping();
