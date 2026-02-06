<?php

use Illuminate\Support\Facades\Schedule;

// في Laravel 11 - ملف routes/console.php
Schedule::command('plans:delete-expired')->dailyAt('00:30');