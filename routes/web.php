<?php

use App\Livewire\PosTerminal;
use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Receipt;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->isAdmin()) {
            return redirect('/admin');
        }
        return redirect('/pos');
    }
    return redirect()->route('login');
});

// POS Login Route
Route::get('/pos/login', \App\Livewire\Auth\PosLogin::class)->name('login')->middleware('guest');

// POS Routes (requires authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/pos', PosTerminal::class)->name('pos');
    Route::get('/pos/receipt/{order}', [ReceiptController::class, 'show'])->name('pos.receipt');
    Route::get('/pos/receipt/{order}/print', [ReceiptController::class, 'print'])->name('pos.receipt.print');
});

// Logout route
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');


