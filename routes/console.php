<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('pdf-report:clear')->everyFourHours();
