<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Models\Setting;
use App\Http\Controllers\PageController;
use App\Services\RaceCapacityService;

// Rotta per inviare i dati del modulo (POST)
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

// Pagamento carta interno con Stripe Elements
Route::get('/checkout/{order_code}/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');

// Rotta per il ritorno DOPO il pagamento confermato (GET)
Route::get('/checkout/success/{order_code}', [CheckoutController::class, 'success'])->name('checkout.success');

// Rotta per il ritorno in caso di ANNULLAMENTO (GET)
Route::get('/checkout/cancel/{order}', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

// Rotta per il download on-demand del singolo biglietto PDF (GET)
Route::get('/ticket/{unique_code}/download', [CheckoutController::class, 'downloadTicket'])->name('ticket.download');

Route::get('/iscrizione', function (RaceCapacityService $raceCapacity) {
    // Recuperiamo il prezzo dal database, con il paracadute a 28€ se non esiste
    try {
        $currentPrice = Setting::where('key', 'ticket_price')->value('value') ?? 28.00;
    } catch (Throwable $e) {
        $currentPrice = 28.00;
    }

    $raceAvailability = $raceCapacity->availability();

    return view('welcome', compact('currentPrice', 'raceAvailability'));
})->name('iscrizione');

Route::get('/api/race-availability', function (RaceCapacityService $raceCapacity) {
    return response()->json([
        'races' => $raceCapacity->availability(),
        'updated_at' => now()->toIso8601String(),
    ]);
})->name('race.availability');

Route::redirect('/ticketing', '/iscrizione', 301);
Route::redirect('/ticketing/index.php', '/iscrizione', 301);


use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

// Rotte Pubbliche per il Login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

use App\Http\Controllers\WebhookController;

Route::post('/webhook/stripe', [WebhookController::class, 'handleStripe'])->name('webhook.stripe');

// 🔒 GRUPPO ROTTE PROTETTE (Lucchetto)
Route::middleware('auth')->group(function () {
    
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/price', [AdminController::class, 'updatePrice'])->name('admin.update_price');
    Route::post('/admin/race-capacities', [AdminController::class, 'updateRaceCapacities'])->name('admin.update_race_capacities');
    
    Route::get('/admin/pending', [AdminController::class, 'pending'])->name('admin.pending');
    Route::post('/admin/upload-csv', [AdminController::class, 'uploadCsv'])->name('admin.upload_csv');
    Route::post('/admin/ticket/{id}/approve', [AdminController::class, 'approveTicket'])->name('admin.approve_ticket');
    Route::delete('/admin/ticket/{id}/delete', [AdminController::class, 'deleteTicket'])->name('admin.delete_ticket');
    
    Route::get('/admin/ticket/create', [AdminController::class, 'createTicket'])->name('admin.create_ticket_form');
    Route::post('/admin/ticket/store', [AdminController::class, 'storeTicket'])->name('admin.store_ticket');
    
    // Esportazione CSV
    Route::get('/admin/export-csv', [AdminController::class, 'exportCsv'])->name('admin.export_csv');
    
    // Azioni sui singoli biglietti
    Route::get('/admin/ticket/{id}/edit', [AdminController::class, 'editTicket'])->name('admin.edit_ticket');
    Route::post('/admin/ticket/{id}/update', [AdminController::class, 'updateTicketData'])->name('admin.update_ticket');
    Route::get('/admin/ticket/{id}/pdf', [AdminController::class, 'downloadPdf'])->name('admin.download_pdf');
});

Route::get('/', [PageController::class, 'show'])->name('home');
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', 'come-arrivare|contattaci|contatti|cookie-policy|gallery|orario|partnership|percorsi|privacy-policy|regolamento|ricettivita|styleguide|termini-condizioni|come-arrivare\.php|contattaci\.php|contatti\.php|cookie-policy\.php|gallery\.php|orario\.php|partnership\.php|percorsi\.php|privacy-policy\.php|regolamento\.php|ricettivita\.php|styleguide\.php|termini-condizioni\.php');
