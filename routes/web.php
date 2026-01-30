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
use App\Http\Controllers\Frontend\FrontendActivityBookingController;
use App\Http\Controllers\Frontend\TestMailController;
use App\Http\Controllers\Backend\AdminAuthController;
use App\Http\Controllers\Frontend\ResidentBookingHistoryController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Frontend\GreaseTrapController;


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
Route::get('/email-test', [TestMailController::class, 'emailTest'])->name('email.test');

Route::post('/send-email', [TestMailController::class, 'sendEmail'])->name('send.email.test');
route::get('/request-electricity/{unitNo}/{year}/{month}', [ProfileController::class, 'requestElectricity'])->name('request.electricity');

Route::get('/our-team', [NavbarController::class, 'ourTeam'])->name('ourTeam');
Route::get('/gallery', [NavbarController::class, 'gallery'])->name('gallery');

Route::get('/minor-work-permit', [NavbarController::class, 'minorWorkPermit'])->middleware(['auth'])->name('minor.work.permit');

//Work Permit
Route::post('/submit-minor-work-permit', [UserWorkPermitController::class, 'submitMinorWorkPermit'])->middleware(['auth'])->name('submit.minor.work.permit');
Route::get('/forms', [NavbarController::class, 'getAllDownloads'])->name('downloads');
route::get('/request-electricity/{unitNo}/{year}/{month}', [ProfileController::class, 'requestElectricity'])->name('request.electricity');




//Amenity/Activity Booking
Route::get('/booking/list', [FrontendFunctionRoomBookingController::class, 'list'])->name('booking.list');
Route::get('/booking/{type}/{id}', [FrontendFunctionRoomBookingController::class, 'fullDetails'])
    ->name('booking.full.details');
Route::get('/booking-activity/{type}/{activity_id}', [FrontendFunctionRoomBookingController::class, 'fullDetailsActivity'])
    ->name('booking.full.details.activity');

Route::get('/check-unit-tenant/{unitNo}', [FrontendFunctionRoomBookingController::class, 'checkUnitTenant']);

Route::post('/activity-new-booking', [FrontendActivityBookingController::class, 'ActivityNewBooking'])->name('activities.new.booking');
Route::get('/fetch-blocked-dates', [FrontendActivityBookingController::class, 'fetchBlockDates'])->name('DateBlocking');
Route::get('/check-unit-frontend', [FrontendActivityBookingController::class, 'checkUnitBooking'])->name('checkUnitBookingFront');

Route::get('/fetch-available-times-user', [FrontendActivityBookingController::class, 'fetchAvailableTimesUser']);
Route::get('/fetch-end-times-user', [FrontendActivityBookingController::class, 'fetchEndTimesUser']);
Route::get('/fetch-available-slots-user', [FrontendActivityBookingController::class, 'fetchAvailableSlotsUser']);
Route::get('/resident/activity-booking/details/{id}', [FrontendActivityBookingController::class, 'getActivityBookingDetails'])
    ->name('resident.activity.bookings.details');

Route::post('/resident/activity-booking/cancel/{booking}', [FrontendActivityBookingController::class, 'cancelAmenityBooking'])
    ->name('activity-booking.cancel');
Route::get('/amenity-booking-details/{id}', [FrontendActivityBookingController::class, 'showAmenityBookingDetails'])
    ->name('show.amenity.booking.details');

Route::get('/check-auth', function () {
    return response()->json([
        'authenticated' => Auth::check()
    ]);
});
Route::post('/send', [ContactController::class, 'send'])
    ->name('contact.send')
    ->middleware('throttle:5,1');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //SOA
    Route::get('/profile/soa', [ProfileController::class, 'soa'])->name('soa');
    // Route::get('/request-soa', [ProfileController::class, 'Reqsoa'])->name('request.soa');
    Route::post('/generate-soa', [ProfileController::class, 'GenerateSoa'])->name(name: 'generate.soa');
    Route::get('/soa/view/{token}', [ProfileController::class, 'view']);


    Route::get('/resident-booking-history', [ResidentBookingHistoryController::class, 'ResidentsBookingHistory'])
        ->name('resident.booking.history');



    // Function Room Booking
    Route::post('/booking/store', [FrontendFunctionRoomBookingController::class, 'store'])->name('booking.store');
    Route::get('/booking-details/{id}', [FrontendFunctionRoomBookingController::class, 'showFunctionRoomBookingDetails'])
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


    //Grease Trap Booking
    Route::get('/grease-trap-booking', [GreaseTrapController::class, 'greeseTrap'])->name('grease.trap.booking');
    Route::post('/grease-trap-booking/store', [GreaseTrapController::class, 'storeGreaseTrapBooking'])->name('grease.trap.booking.store');
    Route::get('/grease-trap/booked-slots', [GreaseTrapController::class, 'getBookedSlots'])
        ->name('grease.trap.booked.slots');
});

Route::middleware('web')
    ->prefix('admin')
    ->group(base_path('routes/admin.php'));

require __DIR__ . '/auth.php';
