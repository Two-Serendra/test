<?php

use App\Http\Controllers\Backend\AdminAuthController;
use App\Http\Controllers\Backend\AmenitiesController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\DownloadsController;
use App\Http\Controllers\Backend\ResidentDetailsController;
use App\Http\Controllers\Backend\EventsController;
use App\Http\Controllers\Backend\FunctionRoomsController;
use App\Http\Controllers\Backend\GalleryController;
use App\Http\Controllers\Backend\ResidenceRequestController;
use App\Http\Controllers\Backend\ServicesController;
use App\Http\Controllers\Backend\UsersController;
use App\Http\Controllers\Backend\ActivitiesController;
use App\Http\Controllers\Backend\WorkPermitController;
use App\Http\Controllers\Backend\FunctionRoomDiscountController;
use App\Models\ResidentDetails;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\FunctionRoomBookingController;
use App\Http\Controllers\Backend\AddOnsController;
use App\Http\Controllers\Frontend\FrontendFunctionRoomBookingController;

Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
});

Route::middleware('auth:admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin-get-function-room-booking-stats', [DashboardController::class, 'getFunctionRoomBookingStats']);
    Route::get('/admin-services', [ServicesController::class, 'services'])->name('admin.services');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // REQUESTS
    // Route::get('/admin-residence-request', [ResidenceRequestController::class, 'showResidenceRequests'])->name('admin.show.residence.requests');
    // Route::post('/admin-add-residence', [ResidenceRequestController::class, 'addResidenceRequests'])->name('admin.add.new.residence');
    // Route::get('/admin-search-residence-request', [ResidenceRequestController::class, 'searchResidenceRequests'])->name('admin.search.residence.requests');
    // Route::get('/get-updated-residence-table', [ResidenceRequestController::class, 'getUpdatedResidenceTable'])->name('get.updated.residence.table');
    // Route::get('/admin-fetch-residence/{id}', [ResidenceRequestController::class, 'fetchResidence'])->name('admin.fetch.residence');
    // Route::post('/admin-residence-request/{id}/status', [ResidenceRequestController::class, 'updateStatus'])->name('admin.update.residence.request.status');
    // Route::post('/admin/update-residence', [ResidenceRequestController::class, 'updateResidence'])->name('admin.update.residence');
    // Route::get('/admin-users-emails', [ResidenceRequestController::class, 'fetchUserEmails'])->name('admin.users.emails');


    // SERVICES
    Route::get('/admin-services', [ServicesController::class, 'services'])->name('admin.services');
    Route::get('/admin-search-services', [ServicesController::class, 'searchService'])->name('search.services');
    Route::post('/admin-new-services', [ServicesController::class, 'newServices'])->name('new.services');
    Route::get('/admin-fetch-service/{id}', [ServicesController::class, 'fetchService'])->name('fetch.service');
    Route::post('/admin-update-services', [ServicesController::class, 'updateService'])->name('update.services');
    Route::delete('/admin-delete-services', [ServicesController::class, 'deleteService'])->name('delete.services');
    Route::get('/get-updated-services-table', [ServicesController::class, 'getUpdatedServicesTable'])->name('get.updated.services.table');


    //DOWNLOADS
    Route::get('/admin-downloads', [DownloadsController::class, 'download'])->name('admin.downloads');
    Route::get('/admin-search-downloads', [DownloadsController::class, 'searchDownload'])->name('search.downloads');
    Route::post('/admin-upload-file', [DownloadsController::class, 'uploadFile'])->name('admin.upload.file');
    Route::delete('/admin-delete-file/{id}', [DownloadsController::class, 'deleteFile'])->name('admin.delete.file');
    Route::get('/get-updated-downloads-table', [DownloadsController::class, 'getUpdatedDownloadsTable'])->name('get.updated.downloads.table');

    //USERS
    Route::get('/admin-users', [UsersController::class, 'showUser'])->name('admin.show.user');
    Route::get('/admin-search-user', [UsersController::class, 'searchUser'])->name('admin.search.user');
    Route::post('/admin-store-user', [UsersController::class, 'storeUser'])->name('admin.store.user');
    Route::get('/admin-fetch-user/{id}', [UsersController::class, 'fetchUser'])->name('admin.fetch.user');
    Route::post('/admin-update-user/{id}', [UsersController::class, 'updateUser'])->name('admin.update.user');
    Route::delete('/admin-delete-user', [UsersController::class, 'deleteUser'])->name('delete.user');
    Route::get('/get-updated-users-table', [UsersController::class, 'getUpdatedUserTable'])->name('admin.get.updated.usertable');

    //Resident Details
    Route::get('/admin-emails', [ResidentDetailsController::class, 'showResidentDetails'])->name('admin.show.resident.details');
    Route::post('/admin-upload-resident-details', [ResidentDetailsController::class, 'uploadResidentDetails'])->name('upload.resident.details');
    Route::get('/admin-fetch-resident-details/{id}', [ResidentDetailsController::class, 'fetchResidentDetails'])->name('fetch.resident.details');
    Route::post('/admin-update-emails', [ResidentDetailsController::class, 'updateEmail'])->name('update.emails');
    Route::get('/get-updated-resident-details-table', [ResidentDetailsController::class, 'getUpdatedResidentDetailsTable'])->name('get.updated.resident.details.table');
    Route::get('/admin-search-email', [ResidentDetailsController::class, 'searchEmails'])->name('admin.search.email');
    Route::delete('/admin-delete-emails', [ResidentDetailsController::class, 'deleteEmail'])->name('delete.emails');


    //Minor Permit
    Route::get('/admin-minor-work-permit', [WorkPermitController::class, 'minorWorkPermit'])->name('admin.show.minor.work.permit');
    Route::get('/admin-search-walkin-work-permit', [WorkPermitController::class, 'searchMinorWorkPermit'])->name('search.admin.minor.work.permit');

    //Walkin Permit 
    Route::get('/admin-search-minor-work-permit', [WorkPermitController::class, 'searchMinorWorkPermit'])->name('search.admin.minor.work.permit');
    Route::get('/admin-walkin-work-permit', [WorkPermitController::class, 'walkinWorkPermit'])->name('admin.show.walkin.work.permit');
    Route::get('/admin-search-walkin-work-permit', [WorkPermitController::class, 'searchWalkinWorkPermit'])->name('search.admin.walkin.work.permit');
    Route::post('/admin-walkin-work-permit', [WorkPermitController::class, 'storeWorkPermit'])->name('admin.store.walkin.work.permit');
    Route::get('/admin-fetch-walkin-work-permit/{id}', [WorkPermitController::class, 'fetchWalkinWorkPermit'])->name('admin.fetch.walkin.work.permit');
    Route::post('/admin-update-walkin-work-permit', [WorkPermitController::class, 'updateWalkinWorkPermit'])->name('admin.update.walkin.work.permit');
    Route::delete('/admin-delete-walkin-work-permit', [WorkPermitController::class, 'deleteWalkinWorkPermit'])->name('delete.walkin.work.permit');
    Route::get('/get-updated-walkin-work-permit-table', [WorkPermitController::class, 'getUpdatedWalkinWorkPermitTable'])->name('get.updated.walkin.work.permit.table');
    Route::post('/admin-download-walkin-work-permit', [WorkPermitController::class, 'downloadWalkinWorkPermit'])->name('admin.download-walkin-work-permit');

    //Amenities
    Route::get('/amenities', [AmenitiesController::class, 'amenities'])->name('admin.show.amenities');
    Route::post('/add-amenities', [AmenitiesController::class, 'addAmenities'])->name('admin.store.amenities');
    Route::get('/fetch/amenity/{id}', [AmenitiesController::class, 'fetchInfoAmenity'])->name('fetchInfoAmenity');
    Route::get('/get-updated-amenities-table', [AmenitiesController::class, 'getUpdatedAmenitiesTable'])->name('getUpdatedAmenitiesTable');
    Route::post('/update-amenities', [AmenitiesController::class, 'updateAmenities'])->name('amenitiesUpdate');
    Route::get('/fetch/amenity_add_remarks/{id}', [AmenitiesController::class, 'fetchAmenityAddRemarks'])->name('fetchAmenityAddRemarks');
    Route::post('/add-remarks-amenities', [AmenitiesController::class, 'addRemarks'])->name('addRemarks');
    Route::post('/show-amenities', [AmenitiesController::class, 'showAmenities'])->name('showAmenity');
    Route::get('/search-amenities', [AmenitiesController::class, 'searchAmenity'])->name('admin.search.amenities');

    //Activities
    Route::get('/activities', [ActivitiesController::class, 'activities'])->name('admin.show.activities');
    Route::post('/add-activities', [ActivitiesController::class, 'addActivities'])->name('activitiesAdd');
    Route::get('/get-updated-activities-table', [ActivitiesController::class, 'getUpdatedActivitiesTable'])->name('getUpdatedActivitiesTable');
    Route::get('/fetch/activity/{id}', [ActivitiesController::class, 'fetchInfoActivity'])->name('fetchInfoActivity');
    Route::post('/update-activities', [ActivitiesController::class, 'updateActivities'])->name('activitiesUpdate');
    Route::get('/fetch/activity_add_remarks/{id}', [ActivitiesController::class, 'fetchActivityAddRemarks'])->name('fetchActivityAddRemarks');
    Route::post('/deactivate-activities', [ActivitiesController::class, 'deactivateActivities'])->name('deactivateActivities');
    Route::post('/activate-activities', [ActivitiesController::class, 'activateActivities'])->name('activateActivities');
    Route::get('/delete-activities', [ActivitiesController::class, 'deleteActivities'])->name('deleteActivities');
    Route::get('/search-activities', [ActivitiesController::class, 'searchActivity'])->name('admin.search.activities');
    Route::get('/date-blocking', [ActivitiesController::class, 'fetchDateBlocking'])->name('admin.show.date.blocking');
    Route::get('/search-block-dates', [ActivitiesController::class, 'searchBlockdDates'])->name('admin.search.block.dates');
    Route::post('/new-date-blocking', [ActivitiesController::class, 'newDateBlocking'])->name('admin.new.date.blocking');
    Route::get('/fetch-blocked-dates', [ActivitiesController::class, 'fetchBlockDates'])->name('DateBlocking');

    //Activities Bookings
    Route::get('get-updated-activities-blocking', [ActivitiesController::class, 'getUpdatedBlockingTable'])->name('get.updated.blocking.table');
    Route::get('/admin-activity-booking', [ActivitiesController::class, 'AdminBookingActivities'])->name('admin.booking.activities');
    Route::get('/search-booking', [ActivitiesController::class, 'searchBooking'])->name('admin.search.booking');
    Route::post('/admin-new-booking', [ActivitiesController::class, 'AdminNewBookingActivities'])->name('admin.new.booking.activities');
    Route::get('/fetch-all-slots-admin', [ActivitiesController::class, 'fetchAllSlotsAdmin'])->name('fetchAllSlotsAdmin');
    Route::get('/fetch-blocked-dates', [ActivitiesController::class, 'fetchBlockDates'])->name('DateBlocking');
    Route::get('/fetch-available-times', [ActivitiesController::class, 'fetchAvailableTimes']);
    Route::get('/fetch-end-times', [ActivitiesController::class, 'fetchEndTimes']);
    Route::get('/fetch-available-slots', [ActivitiesController::class, 'fetchAvailableSlots'])->name('fetchAvailableSlots');
    Route::get('/check-unit-booking', [ActivitiesController::class, 'checkUnitBooking'])->name('checkUnitBooking');
    Route::get('get-updated-bookings-table', [ActivitiesController::class, 'getUpdatedBookingTable'])->name('getUpdatedBookingTable');
    Route::get('/fetch/booking/{id}', [ActivitiesController::class, 'fetchInfoBooking'])->name('fetchInfoBooking');
    Route::post('/cancel-booking', [ActivitiesController::class, 'cancelBooking'])->name('cancelBooking');
    Route::get('/history', [ActivitiesController::class, 'history'])->name('admin.activity.history');
    Route::get('/search-histories', [ActivitiesController::class, 'searchHistory'])->name('search-history');
    Route::post('/download-history', [ActivitiesController::class, 'downloadHistory'])->name('download-history');

    //Function Rooms
    Route::get('/admin-function-rooms', [FunctionRoomsController::class, 'showFunctionRooms'])->name('admin.show.function.rooms');
    Route::post('/admin-store-function-rooms', [FunctionRoomsController::class, 'storeFunctionRooms'])->name('admin.store.function.rooms');
    Route::get('/admin-search-function-rooms', [FunctionRoomsController::class, 'searchFunctionRooms'])->name('admin.search.function.rooms');
    Route::get('/admin-fetch-function-rooms/{id}', action: [FunctionRoomsController::class, 'fetchFunctionRooms'])->name('admin.fetch.function.rooms');
    Route::post('/admin-update-function-rooms', [FunctionRoomsController::class, 'updateFunctionRooms'])->name('admin.update.function.rooms');
    Route::delete('/admin-delete-function-rooms', [FunctionRoomsController::class, 'deleteFunctionRooms'])->name('delete.function.rooms');
    Route::post('/admin-function-rooms/disable/{id}', [FunctionRoomsController::class, 'disable']);
    Route::post('/admin-function-rooms/enable/{id}', [FunctionRoomsController::class, 'enable']);
    Route::get('/get-updated-function-rooms-table', [FunctionRoomsController::class, 'getUpdatedFunctionRoomsTable'])->name('get.updated.function.rooms.table');

    //Function Rooms Discounts
    Route::get('/admin-function-room-discounts', [FunctionRoomDiscountController::class, 'showFunctionRoomDiscounts'])->name('admin.show.function.room.discounts');
    Route::post('/admin-create-function-room-discounts', [FunctionRoomDiscountController::class, 'createFunctionRoomDiscounts'])->name('create.function.room.discounts');
    Route::get('/get-updated-function-room-discount-table', [FunctionRoomDiscountController::class, 'getUpdatedFunctionRoomDiscountTable']);
    Route::delete('/admin-delete_function_room_discounts', [FunctionRoomDiscountController::class, 'deleteFunctionRoomDiscounts']);
    Route::get('/admin-fetch-function-room-discount/{id}', [FunctionRoomDiscountController::class, 'fetchFunctionRoomDiscounts']);
    Route::post('/admin-update-function-room-discount', [FunctionRoomDiscountController::class, 'updateFunctionRoomDiscount'])->name(name: 'admin.update.function.room.discount');


    // Add Ons
    Route::get('/admin-add-ons-table', [AddOnsController::class, 'showAddOns'])->name('admin.show.add.ons');
    Route::post('/admin-store-add-ons-', [AddOnsController::class, 'storeAddOns'])->name('admin.store.add.ons');
    Route::get('/admin-fetch-add-ons/{id}', [AddOnsController::class, 'fetchAddOns'])->name('admin.fetch.add.ons');
    Route::post('/admin-update-add-ons', [AddOnsController::class, 'updateAddOns'])->name('admin.update.add.ons');
    Route::get('/get-updated-add-ons-table', [AddOnsController::class, 'getUpdatedAddOnsTable'])->name('get.updated.add.ons.table');
    Route::delete('/admin-delete-add-ons', [AddOnsController::class, 'deleteAddOns']);
    Route::post('/admin-add-ons/disable/{id}', [AddOnsController::class, 'disable']);
    Route::post('/admin-add-ons/enable/{id}', [AddOnsController::class, 'enable']);


    //Function Room Date Blocking
    Route::get('/admin-show-function-room-date-blocking-table', [FunctionRoomsController::class, 'showFunctionRoomDateBlockingTable'])
        ->name('admin.show.function.rooms.date.blocking');
    Route::post('/admin-new-function-room-date-blocking', [FunctionRoomsController::class, 'newDateBlocking'])->name('new.function.room.date.blocking');
    Route::get('/admin-fetch-function-room-blocked-dates', [FunctionRoomsController::class, 'fetchFunctionRoomBlockDates'])->name('fetch.function.room.block.dates');
    Route::get('/admin-get-updated-function-room-date-blocking', [FunctionRoomsController::class, 'getUpdatedFunctionRoomBlockingTable'])->name('get.updated.function.room.blocking.table');
    Route::delete('/admin-delete-date-blocking', [FunctionRoomsController::class, 'deleteDateBlocking']);



    //Function Room Bookings
    Route::get('/admin-function-room-bookings', [FunctionRoomBookingController::class, 'showFunctionRoomBookings'])->name('admin.show.function.room.bookings');
    // Route::get('/admin-function-room-bookings-store', [FunctionRoomBookingController::class, 'adminBookingStore'])->name('admin.booking.store');

    Route::post('/admin-function-room-bookings-approval', [FunctionRoomBookingController::class, 'FunctionRoomBookingApproval'])->name('admin.function.room.booking.approvals');
    Route::post('/admin-function-room-bookings-rejection', action: [FunctionRoomBookingController::class, 'FunctionRoomBookingReject']);
    Route::get('/admin-function-room-bookings/{id}/details', action: [FunctionRoomBookingController::class, 'getFunctionRoomBookingDetails'])
        ->name('admin.get.function.room.bookings.details');
    Route::get('/admin-get-updated-function-room-bookings-table', [FunctionRoomBookingController::class, 'getUpdatedFunctionRoomBookingTable'])
        ->name('get.updated.function.room.bookings.table');
    Route::get('/admin-function-room/{id}/booked-dates', [FrontendFunctionRoomBookingController::class, 'getFunctionRoomBookedDates']);
    Route::get('/admin-check-unit-tenant/{unitNo}', [FunctionRoomBookingController::class, 'checkUnitTenant']);
    Route::get('/admin-function-room/addons-availability', [FunctionRoomBookingController::class, 'getAddOnsAvailability']);
    Route::get('/admin-bookings/{id}/edit', [FunctionRoomBookingController::class, 'editFunctionRoomBooking']);
    Route::post('/admin-update-function-room-booking', [FunctionRoomBookingController::class, 'updateFunctionRoomBooking'])
        ->name('admin.update.function.room.booking');
    Route::get('/admin-search-function-room-booking-records', [FunctionRoomBookingController::class, 'searchFunctionRoomBookingRecords'])
        ->name('search.function.room.booking.records');
    Route::get('/admin-function-room-booked-dates/{id}', [FunctionRoomBookingController::class, 'getAdminFunctionRoomBookedDates']);

    Route::get('/admin-search-residents', [FunctionRoomBookingController::class, 'searchResidents'])->name('admin.search.residents');
    Route::get('/admin-check-unit-tenant/{unitNo}', [FunctionRoomBookingController::class, 'adminCheckUnitTenant']);
    Route::post('/admin/booking/store', [FunctionRoomBookingController::class, 'AdminStoreBooking'])->name('admin.booking.store');
    // Add Ons
    Route::get('admin-get-function-room-addons-availability', [FunctionRoomBookingController::class, 'getAdminAddonsAvailability']);
    //Function Room Bookings Records
    Route::get('/admin-function-room-booking-records', action: [FunctionRoomBookingController::class, 'showFunctionRoomBookingRecords'])->name('admin.show.function.room.booking.records');

    Route::post('/admin-download-function-room-booking-records', [FunctionRoomBookingController::class, 'downloadFunctionRoomBookingRecords'])
        ->name('download.function.room.booking.records');


    //dow   
    Route::get('/admin-gallery', [GalleryController::class, 'showGallery'])->name('admin.show.gallery');
    Route::post('/admin/gallery/upload', [GalleryController::class, 'uploadGalleryImages'])->name('admin.gallery.upload');
    Route::get('/get-updated-gallery-table', [GalleryController::class, 'getUpdatedGalleryTable'])->name('get.updated.gallery.table');

    //Event
    Route::get('/admin-events', [EventsController::class, 'showEvents'])->name('admin.show.events');
    Route::post('/admin-store-events', [EventsController::class, 'storeEvents'])->name('admin.store.events');
    Route::get('/admin-fetch-events/{id}', action: [EventsController::class, 'fetchEvents'])->name('admin.fetch.events');
    Route::post('/admin-update-events', [EventsController::class, 'updateEvents'])->name('admin.update.events');
    // Route::get('/admin-search-events', [EventsController::class, 'searchEvents'])->name('admin.search.events');
    Route::delete('/admin-delete-events', [EventsController::class, 'deleteEvents'])->name('delete.events');
    Route::get('/get-updated-events-table', [EventsController::class, 'getUpdatedEventsTable'])->name('getUpdatedEventsTable');






});
