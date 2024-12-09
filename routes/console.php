<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('pdf:clear')->everyFourHours();
