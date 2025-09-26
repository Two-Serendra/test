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
use App\Http\Controllers\Backend\WorkPermitController;
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
    Route::get('/admin-amenities', [AmenitiesController::class, 'showAmenities'])->name('admin.show.amenities');
    Route::post('/admin-store-amenities', [AmenitiesController::class, 'storeAmenities'])->name('admin.store.amenities');
    Route::post('/admin-update-amenities', [AmenitiesController::class, 'updateAmenities'])->name('admin.update.amenities');
    Route::get('/admin-search-amenities', [AmenitiesController::class, 'searchAmenities'])->name('admin.search.amenities');
    Route::get('/get-updated-amenities-table', [AmenitiesController::class, 'getUpdatedAmenitiesTable'])->name('getUpdatedAmenitiesTable');


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

    Route::get('/admin-get-updated-function-room-date-blocking', [FunctionRoomsController::class, 'getUpdatedFunctionRoomBlockingTable'])->name('get.updated.function.room.blocking.table');



    //Function Room Bookings
    Route::get('/admin-function-room-bookings', [FunctionRoomBookingController::class, 'showFunctionRoomBookings'])->name('admin.show.function.room.bookings');
    Route::get('/admin-function-room-bookings-approval', [FunctionRoomBookingController::class, 'FunctionRoomBookingApproval'])->name('admin.function.room.booking.approvals');
    Route::get('/admin-function-room-bookings/{id}/details', [FunctionRoomBookingController::class, 'getFunctionRoomBookingDetails'])
        ->name('admin.get.function.room.bookings.details');
    Route::get('/admin-get-updated-function-room-bookings-table', [FunctionRoomBookingController::class, 'getUpdatedFunctionRoomBookingTable'])
        ->name('get.updated.function.room.bookings.table');
    Route::get('/admin-function-room/{id}/booked-dates', [FrontendFunctionRoomBookingController::class, 'getFunctionRoomBookedDates']);
    Route::get('/admin-check-unit-tenant/{unitNo}', [FunctionRoomBookingController::class, 'checkUnitTenant']);
    Route::get('/admin-function-room/addons-availability', [FunctionRoomBookingController::class, 'getAddOnsAvailability']);
    Route::get('/admin-bookings/{id}/edit', [FunctionRoomBookingController::class, 'editFunctionRoomBooking']);
    Route::post('/admin-update-function-room-booking', [FunctionRoomBookingController::class, 'updateFunctionRoomBooking'])
        ->name('admin.update.function.room.booking');

    //Function Room Bookings Records
    Route::get('/admin-function-room-booking-records', action: [FunctionRoomBookingController::class, 'showFunctionRoomBookingRecords'])->name('admin.show.function.room.booking.records');

    Route::get('/admin-search-function-room-booking-records', [FunctionRoomBookingController::class, 'searchFunctionRoomBookingRecords'])
        ->name('search.function.room.booking.records');
    Route::post('/admin-download-function-room-booking-records', [FunctionRoomBookingController::class, 'downloadFunctionRoomBookingRecords'])
        ->name('download.function.room.booking.records');


    //Gallery
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





    Route::get('/admin-fetch-function-room-blocked-dates', [FunctionRoomsController::class, 'fetchFunctionRoomBlockDates'])->name('fetch.function.room.block.dates');

});
