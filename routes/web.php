<?php

use App\Http\Controllers\Backend\ServicesController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\UserActionsController;
use App\Http\Controllers\Frontend\UserNotificationController;
use App\Http\Controllers\Frontend\UserWorkPermitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Frontend\NavbarController;
use App\Http\Controllers\Frontend\UserProfileController;
use App\Http\Controllers\Frontend\FrontendFunctionRoomBookingController;
use App\Http\Controllers\Backend\AdminAuthController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Auth\RegisteredUserController;


use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('home');
});

Route::get('/register', [RegisteredUserController::class, 'create'])
    ->middleware('guest')
    ->name('register');

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest');


// Define this route separately
Route::get('/home', [NavbarController::class, 'home'])->name('home');
Route::get('/about', [NavbarController::class, 'about'])->name('about');
Route::get('/services', [NavbarController::class, 'services'])->name('services');
Route::get('/contact', [NavbarController::class, 'contact'])->name('contact');
Route::get('/downloadables', [NavbarController::class, 'downloadables'])->name('downloadables');
Route::get('/sections', [NavbarController::class, 'sections'])->name('sections');
Route::get('/maps', [NavbarController::class, 'maps'])->name('maps');
Route::get('/events', [NavbarController::class, 'events'])->name('events');
Route::get('/events/{id}', [NavbarController::class, 'showEventDetails'])->name('show.event.details');



Route::get('/our-team', [NavbarController::class, 'ourTeam'])->name('ourTeam');
Route::get('/gallery', [NavbarController::class, 'gallery'])->name('gallery');

Route::get('/minor-work-permit', [NavbarController::class, 'minorWorkPermit'])->middleware(['auth'])->name('minor.work.permit');

//Work Permit
Route::post('/submit-minor-work-permit', [UserWorkPermitController::class, 'submitMinorWorkPermit'])->middleware(['auth'])->name('submit.minor.work.permit');
Route::get('/forms', [NavbarController::class, 'getAllDownloads'])->name('downloads');

//Booking
Route::get('/booking/list', [FrontendFunctionRoomBookingController::class, 'list'])->name('booking.list');
Route::get('/booking/{type}/{id}', [FrontendFunctionRoomBookingController::class, 'fullDetails'])
    ->name('booking.full.details');
Route::get('/check-unit-tenant/{unitNo}', [FrontendFunctionRoomBookingController::class, 'checkUnitTenant']);


// Route::get('/downloads', [NavbarController::class, 'getAllDownloads'])->middleware(['auth'])->name('downloads');Residence
Route::get('/check-auth', function () {
    return response()->json([
        'authenticated' => Auth::check()
    ]);
});
Route::post('/send', [ContactController::class, 'send'])
    ->name('contact.send')
    ->middleware('throttle:5,1');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // Function Room Booking
    Route::post('/booking/store', [FrontendFunctionRoomBookingController::class, 'store'])->name('booking.store');
    Route::get('/booking-details/{booking}', [FrontendFunctionRoomBookingController::class, 'showFunctionRoomBookingDetails'])
        ->name('show.functionroom.booking.details');
    Route::get('/function-room/{id}/booked-dates', [FrontendFunctionRoomBookingController::class, 'getFunctionRoomBookedDates']);
    Route::get('/view-function-room-bookings/{id}/details', [FrontendFunctionRoomBookingController::class, 'getFunctionRoomBookingDetails'])
        ->name('get.function.room.bookings.details');
    
    // Add Ons
    Route::get('/function-room/addons-availability', [FrontendFunctionRoomBookingController::class, 'getAddOnsAvailability']);

    Route::get('/notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}', [UserNotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/{id}/read', function ($id) {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    })->name('notifications.read')->middleware('auth');
    Route::post('/booking/{id}/cancel', [FrontendFunctionRoomBookingController::class, 'cancel'])->name('booking.cancel');

});


Route::middleware('web')
    ->prefix('admin')
    ->group(base_path('routes/admin.php'));

require __DIR__ . '/auth.php';
