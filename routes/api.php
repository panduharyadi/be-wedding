<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Owner\OwnerController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Customer\RatingController;
use App\Http\Controllers\Customer\RequestJadwalController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', function (\Illuminate\Http\Request $request) {
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Logged out successfully'
    ]);
})->middleware('auth:sanctum');

// me endpoint
Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    $user = $request->user();
    return response()->json([
        'status' => 'success',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->getRoleNames()->first(),
        ]
    ]);
});

// route landing
Route::get('/paket', [LandingController::class, 'getPaket']);

// admin
Route::middleware(['auth:sanctum', 'role:admin'])->controller(AdminController::class)->group(function () {
    Route::get('/admin/count/customer', 'countCustomerAktif');

    // Route Paket
    Route::get('/admin/paket', 'getPaketAdmin');
    
    // Route Silabus
    Route::post('/{paketId}/admin/silabus', 'storeSilabus');
    Route::get('/admin/silabus/{paketId}', 'getSilabus');

    // payment
    Route::get('/admin/payment/list', 'getPayment');

    // Route Reschedule
    Route::get('/admin/reschedule', 'getReschedule');
    Route::post('/admin/reschedule/approve/{id}', 'approveReschedule');
    Route::post('/admin/reschedule/reject/{id}', 'rejectReschedule');

    // Route Chat
    Route::get('/admin/customer', 'getAllCustomer');
    Route::get('/admin/chat/{customerId}', 'getChat');
    Route::post('/admin/chat/send', 'sendMessage');
});

// owner
Route::middleware(['auth:sanctum', 'role:owner'])->controller(OwnerController::class)->group(function () {
    // Route Paket
    Route::get('/owner/paket', 'getAllPaket');
    Route::post('/owner/paket', 'storePaket');
    Route::get('/owner/paket/{id}', 'detailPaket');
    Route::put('/owner/paket/{id}', 'updatePaket');
    Route::delete('/owner/paket/{id}', 'deletePaket');

    // rating
    Route::get('/owner/ratings', 'rating');

    // sales report
    Route::get('/owner/sales/report', 'salesReport');

    // Route Employee Management
    Route::get('/owner/employee', 'getAllEmployee');
    Route::post('/owner/employee', 'addEmployee');
    Route::delete('/owner/employee/{id}', 'removeEmployee');
});

// customer
Route::middleware(['auth:sanctum', 'role:customer'])->controller(CustomerController::class)->group(function () {
    Route::get('/customer/dashboard', 'index')->name('customer.dashboard');

    // purchasing routes
    Route::get('/customer/purchasing', 'getPurchasingPaket');
    Route::get('/customer/purchasing/silabus', 'getPurchasedSilabus');

    // chat routes
    Route::get('/customer/chat', 'getChat');
    Route::post('/customer/chat/send', 'sendMessage');

    // profile routes
    Route::put('/customer/profile', 'updateProfile');
});

Route::middleware(['auth:sanctum', 'role:customer'])->controller(PaymentController::class)->group(function () {
    // midtrans routes
    Route::post('/customer/midtrans/token', 'getSnapToken');
});

Route::middleware(['auth:sanctum', 'role:customer'])->controller(RatingController::class)->group(function () {
    Route::post('/customer/rating', 'ratingPaket');
    Route::get('/customer/rating/{transaction_id}', 'showRating');
});

Route::middleware(['auth:sanctum', 'role:customer'])->controller(RequestJadwalController::class)->group(function () {
    Route::post('/customer/request/jadwal', 'requestJadwal');
});

// midtrans callback endpoint
Route::post('/customer/midtrans/callback', [PaymentController::class, 'callback']);