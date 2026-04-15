<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Santral\Controllers\SantralController;

Route::group([
    'prefix' => 'isletmeyonetim',
    'middleware' => ['web', 'santral.access']
], function() {
    Route::get('/cdr', [SantralController::class, 'getCdr']);
});