<?php

Schedule::command('vehicles:sync')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

