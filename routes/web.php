<?php

use App\Http\Controllers\Frontend\FrontendAusiBookingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Backend\ServicesController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\FrontendFitnessHubBookingController;
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
use App\Http\Controllers\Frontend\PestControlController;
use App\Http\Controllers\Backend\FitnessHubController;
use App\Http\Controllers\Frontend\FitnessHubBookingController;
use App\Http\Controllers\MobileApp\AusiBookingMobileController;
use App\Http\Controllers\MobileApp\GreaseTrapBookingMobileController;
use App\Http\Controllers\MobileApp\PestControlBookingMobileController;
use App\Http\Controllers\MobileApp\FaqsMobileController;
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


Route::post('/register', [RegisteredUserController::class, 'storeUser'])->name('register.user')->middleware('guest');



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
Route::get('/Links', [NavbarController::class, 'getAllLinks'])->name('links');
route::get('/request-electricity/{unitNo}/{year}/{month}', [ProfileController::class, 'requestElectricity'])->name('request.electricity');


//Amenity/Activity Booking
Route::post('/send', [ContactController::class, 'send'])
    ->name('contact.send')
    ->middleware('throttle:5,1');

//SOA
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile/soa', [ProfileController::class, 'soa'])->name('soa');
    Route::get('/check-auth', function () {
        return response()->json([
            'authenticated' => Auth::check()
        ]);
    });
});

// Route::get('/request-soa', [ProfileController::class, 'Reqsoa'])->name('request.soa');
Route::post('/generate-soa', [ProfileController::class, 'GenerateSoa'])->name(name: 'generate.soa');
Route::get('/soa/view/{token}', [ProfileController::class, 'view']);
Route::post('/send-token', [RegisteredUserController::class, 'sendToken'])
    ->name('send.token')
    ->middleware('throttle:3,1');



Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/resident-booking-history', [ResidentBookingHistoryController::class, 'ResidentsBookingHistory'])
        ->name('resident.booking.history');

    //Grease Trap Booking
    Route::get('/grease-trap-booking', [GreaseTrapController::class, 'greeseTrap'])->name('grease.trap.booking');
    Route::post('/grease-trap-booking/store', [GreaseTrapController::class, 'storeGreaseTrapBooking'])->name('grease.trap.booking.store')->middleware('throttle:5,1');
    Route::get('/grease-trap/booked-slots', [GreaseTrapController::class, 'getBookedSlots'])
        ->name('grease.trap.booked.slots');
    Route::post('/grease-trap-booking/cancel/{booking}', [GreaseTrapController::class, 'CancelGreaseTrapBooking'])
        ->name('grease.trap.booking.cancel')->middleware('throttle:5,1');
    Route::get('/grease-trap-booking-details/{id}', [GreaseTrapController::class, 'showGreaseTrapBookingDetails'])
        ->name('show.grease.trap.booking.details');
    Route::get('/resident-booking-reload-table', [GreaseTrapController::class, 'GreaseTrapBookiingTableReload'])->name('grease.trap.booking.table.reload');

    //Pest Control Booking
    Route::get('/pest-control-booking', [PestControlController::class, 'pestControl'])->name('pest.control.booking');
    Route::post('/pest-control-booking/store', [PestControlController::class, 'storePestControlBooking'])->name('pest.control.booking.store');

    Route::get('/pest-control/booked-slots', [PestControlController::class, 'getBookedSlotsPestControl'])
        ->name('pest.control.booked.slots');
    Route::get('/pest-control-booking-details/{id}', [PestControlController::class, 'showPestControlBookingDetails'])
        ->name('show.pest.control.booking.details');
    Route::post('/pest-control-booking/cancel/{booking}', [PestControlController::class, 'CancelPestControlBooking'])
        ->name('pest.control.booking.cancel')->middleware('throttle:5,1');


    //Ausi Booking
    Route::get('/ausi-booking', [FrontendAusiBookingController::class, 'ausiBookingUser'])->name('ausi.booking');
    Route::get('/ausi-booked-slots', [FrontendAusiBookingController::class, 'getBookedSlotsAusi'])->name('ausi.booked.slots');
    Route::post('/ausi-booking/store', [FrontendAusiBookingController::class, 'storeAusiBooking'])->name('ausi.booking.store');
    Route::post('/ausi-booking/cancel/{booking}', [FrontendAusiBookingController::class, 'CancelAusiBooking'])
        ->name('ausi.booking.cancel')->middleware('throttle:5,1');
    Route::get('/ausi-booking-details/{id}', [FrontendAusiBookingController::class, 'showAusiBookingDetails'])
        ->name('show.ausi.booking.details');

    // Function Room Booking
    Route::post('/booking/store', [FrontendFunctionRoomBookingController::class, 'store'])->name('booking.store');
    Route::get('/booking-details/{id}', [FrontendFunctionRoomBookingController::class, 'showFunctionRoomBookingDetails'])
        ->name('show.functionroom.booking.details');
    Route::get('/function-room/{id}/booked-dates', [FrontendFunctionRoomBookingController::class, 'getFunctionRoomBookedDates']);
    Route::get('/view-function-room-bookings/{id}/details', [FrontendFunctionRoomBookingController::class, 'getFunctionRoomBookingDetails'])
        ->name('get.function.room.bookings.details');

    //Fitness Hub Booking
    Route::get('/booking-fitness-hub/{type}/{fitness_hub_id}', [FrontendFitnessHubBookingController::class, 'fullDetailsFitnessHub'])
        ->name('booking.full.details.fitness.hub');
    Route::get('/fetch-date-blocking-fitness-hub-user', [FrontendFitnessHubBookingController::class, 'fetchDateBlockingFitnessHubUser'])->name('fetch.date.blocking.fitness.hub.user');
    Route::get('/check-unit-booking-fitness-hub', [FitnessHubBookingController::class, 'checkUnitBookingFitnessHub'])->name('checkUnitBookingFitnessHub');
    Route::get('/fetch-available-times-fitness-hub', [FitnessHubBookingController::class, 'fetchAvailableTimesFitnessHub'])->name('fetchAvailableTimesFitnessHub');
    Route::get('/fetch-available-end-times-fitness-hub', [FitnessHubBookingController::class, 'fetchAvailableEndTimesFitnessHub'])->name('fetchAvailableEndTimesFitnessHub');
    Route::post('/user-new-booking-fitness-hub', [FitnessHubBookingController::class, 'UserNewBookingFitnessHub'])->name('user.new.booking.fitness.hub');
    Route::get('/resident/fitness-hub-booking/details/{id}', [FitnessHubBookingController::class, 'getFitnessHubBookingDetails'])
        ->name('resident.fitness.hub.bookings.details');
    Route::post('/resident/fitness-hub-booking/cancel/{booking}', [FitnessHubBookingController::class, 'cancelFitnessHubBooking'])
        ->name('fitness.hub.booking.cancel')->middleware('throttle:5,1');
    Route::get('/fitness-hub-booking-details/{id}', [FitnessHubBookingController::class, 'showFitnessHubBookingDetails'])
        ->name('show.fitness.hub.booking.details');
    Route::get('/fetch-all-slots-user-fitness-hub', [FitnessHubBookingController::class, 'fetchAllSlotsUserFitnessHub'])->name('user.fetch.available.slot.fitness.hub');

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


    Route::get('/booking/list', [FrontendFunctionRoomBookingController::class, 'list'])->name('booking.list');
    Route::get('/booking/{type}/{id}', [FrontendFunctionRoomBookingController::class, 'fullDetails'])
        ->name('booking.full.details');
    Route::get('/booking-activity/{type}/{activity_id}', [FrontendFunctionRoomBookingController::class, 'fullDetailsActivity'])
        ->name('booking.full.details.activity');

    Route::get('/check-unit-tenant/{unitNo}', [FrontendFunctionRoomBookingController::class, 'checkUnitTenant']);

    Route::post('/activity-new-booking', [FrontendActivityBookingController::class, 'ActivityNewBooking'])->name('activities.new.booking')->middleware('throttle:5,1');
    Route::get('/check-unit-frontend', [FrontendActivityBookingController::class, 'checkUnitBooking'])->name('checkUnitBookingFront');

    Route::get('/fetch-available-times-user', [FrontendActivityBookingController::class, 'fetchAvailableTimesUser']);
    Route::get('/fetch-end-times-user', [FrontendActivityBookingController::class, 'fetchEndTimesUser']);
    Route::get('/fetch-available-slots-user', [FrontendActivityBookingController::class, 'fetchAvailableSlotsUser']);
    Route::get('/resident/activity-booking/details/{id}', [FrontendActivityBookingController::class, 'getActivityBookingDetails'])
        ->name('resident.activity.bookings.details');

    Route::post('/resident/activity-booking/cancel/{booking}', [FrontendActivityBookingController::class, 'cancelAmenityBooking'])
        ->name('activity-booking.cancel')->middleware('throttle:5,1');
    Route::get('/amenity-booking-details/{id}', [FrontendActivityBookingController::class, 'showAmenityBookingDetails'])
        ->name('show.amenity.booking.details');

    Route::get('/fetch-blocked-dates', [FrontendActivityBookingController::class, 'fetchBlockDates'])->name('DateBlocking');

    Route::get('/fetch-all-slots-user', [FrontendActivityBookingController::class, 'fetchAllSlotsUser'])->name('fetchAllSlotsUser');

});

Route::middleware(['miniapp.webview'])->group(function () {
    Route::get('/mobile-services-booking', [AusiBookingMobileController::class, 'MobileServices'])->middleware('no-cache');
    //AUSI
    Route::get('/ausi-booking-mobile', [AusiBookingMobileController::class, 'ausiBookingUserMobile'])->name('ausi.booking.mobile')->middleware('no-cache');
    Route::get('/ausi-booked-slots-mobile', [AusiBookingMobileController::class, 'getBookedSlotsAusiMobile'])->middleware('no-cache');
    Route::post('/ausi-booking-mobile/store', [AusiBookingMobileController::class, 'storeAusiBookingMobile'])->name('ausi.booking.mobile.store')->middleware('throttle:5,1');
    Route::get('/ausi-booking-mobile/history', [AusiBookingMobileController::class, 'viewAusiBookingMobileHistory'])->name('ausi.booking.mobile.history')->middleware('no-cache');
    Route::get('/get-ausi-booking-mobile/history', [AusiBookingMobileController::class, 'getAusiBookingMobileHistory'])->name('get.ausi.booking.mobile.history')->middleware('no-cache');
    Route::post('/ausi-booking-mobile/cancel/{booking}', [AusiBookingMobileController::class, 'CancelAusiBookingMobile'])
        ->name('ausi.cancel.booking.mobile')->middleware('throttle:5,1');
    Route::get('/ausi-booking-details/{id}', [AusiBookingMobileController::class, 'showAusiBookingDetails'])->middleware('no-cache');
    Route::get('/fetch-ausi-booking-mobile/{id}', [AusiBookingMobileController::class, 'fetchAusiBookingMobile'])->name('fetch.ausi.booking.mobile');

    //Pest Control
    Route::get('/pest-control-booking-mobile', [PestControlBookingMobileController::class, 'pestControlBookingUserMobile'])->name('pest.control.booking.mobile')->middleware('no-cache');
    Route::post('/pest-control-booking-mobile/store', [PestControlBookingMobileController::class, 'storePestControlBookingMobile'])->name('pest.control.booking.mobile.store')->middleware('throttle:5,1');
    Route::get('/pest-control-booked-slots-mobile', [PestControlBookingMobileController::class, 'getBookedSlotsPestControlMobile'])->middleware('no-cache');
    Route::get('/pest-control-booking-mobile/history', [PestControlBookingMobileController::class, 'viewPestControlBookingMobileHistory'])->name('pest.control.booking.mobile.history')->middleware('no-cache');
    Route::get('/get-pest-control-booking-mobile/history', [PestControlBookingMobileController::class, 'getPestControlBookingMobileHistory'])->name('get.pest.control.booking.mobile.history')->middleware('no-cache');
    Route::post('/pest-control-booking-mobile/cancel/{booking}', [PestControlBookingMobileController::class, 'CancelPestControlBookingMobile'])
        ->name('pest.control.cancel.booking.mobile')->middleware('throttle:5,1');

    //Greasetrap
    Route::get('/grease-trap-booking-mobile', [GreaseTrapBookingMobileController::class, 'greasetrapBookingUserMobile'])->name('grease-trap.booking.mobile')->middleware('no-cache');
    Route::post('/grease-trap-booking-mobile/store', [GreaseTrapBookingMobileController::class, 'storeGreaseTrapBookingMobile'])->name('grease.trap.booking.mobile.store')->middleware('throttle:5,1');
    Route::get('/grease-trap-booked-slots-mobile', [GreaseTrapBookingMobileController::class, 'getBookedSlotsGreaseTrapMobile'])->middleware('no-cache');
    Route::get('/grease-trap-disabled-dates-mobile', [GreaseTrapBookingMobileController::class, 'getDisabledGreaseTrapDatesMobile'])->name('grease.trap.disabled.dates.mobile')->middleware('no-cache');
    Route::get('/grease-trap-booking-mobile/history', [GreaseTrapBookingMobileController::class, 'viewGreaseTrapBookingMobileHistory'])->name('grease.trap.booking.mobile.history')->middleware('no-cache');
    Route::get('/get-grease-trap-booking-mobile/history', [GreaseTrapBookingMobileController::class, 'getGreaseTrapBookingMobileHistory'])->name('get.grease.trap.booking.mobile.history')->middleware('no-cache');
    Route::post('/grease-trap-booking-mobile/cancel/{booking}', [GreaseTrapBookingMobileController::class, 'CancelGreaseTrapBookingMobile'])
        ->name('grease.trap.cancel.booking.mobile')->middleware('throttle:5,1');

    Route::get('/subway-news-mobile', [FaqsMobileController::class, 'SubwayNews'])->name('subway.news.mobile');
    Route::get('/subway-faqs-mobile', [FaqsMobileController::class, 'SubwayFaqs'])->name('subway.faqs.mobile');

    Route::match(['GET', 'POST'], '/mobile-debug', function (\Illuminate\Http\Request $request) {

        return response()->json([
            'session_name' => config('session.cookie'),
            'session_id' => session()->getId(),
            'session_token' => session()->token(),

            'has_session_cookie' => $request->hasCookie(config('session.cookie')),

            'cookies' => $request->cookies->all(),

            'headers' => [
                'cookie' => $request->header('Cookie'),
                'origin' => $request->header('Origin'),
                'referer' => $request->header('Referer'),
                'host' => $request->header('Host'),
                'user_agent' => $request->header('User-Agent'),
                'x_csrf_token' => $request->header('X-CSRF-TOKEN'),
            ],

            'is_secure' => $request->isSecure(),
            'url' => $request->fullUrl(),
        ]);
    });

});
Route::middleware('web')
    ->prefix('admin')
    ->group(base_path('routes/admin.php'));

require __DIR__ . '/auth.php';
