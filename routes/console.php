<?php
use App\Data\Models\Person;
use App\Services\ImportCercred;

Artisan::command('cercred:import', function () {
    app(ImportCercred::class)->import($this);
})->describe('Display an inspiring quote');
