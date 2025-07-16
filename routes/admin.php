<?php

use App\Http\Controllers\Backend\AdminAuthController;
use App\Http\Controllers\Backend\AmenitiesController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\DownloadsController;
use App\Http\Controllers\Backend\EmailsController;
use App\Http\Controllers\Backend\EventsController;
use App\Http\Controllers\Backend\FunctionRoomsController;
use App\Http\Controllers\Backend\GalleryController;
use App\Http\Controllers\Backend\ServicesController;
use App\Http\Controllers\backend\UsersController;
use App\Http\Controllers\backend\WorkPermitController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
});

Route::middleware('auth:admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin-services', [ServicesController::class, 'services'])->name('admin.services');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

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
    Route::delete('/admin-delete-user', [UsersController::class, 'deleteUser'])->name('delete.services');
    Route::get('/get-updated-users-table', [UsersController::class, 'getUpdatedUserTable'])->name('admin.get.updated.usertable');

    //EMAILS
    Route::get('/admin-emails', [EmailsController::class, 'showEmail'])->name('admin.show.email');
    Route::post('/admin-upload-email', [EmailsController::class, 'uploadEmail'])->name('upload.email');
    Route::get('/admin-fetch-email/{id}', [EmailsController::class, 'fetchEmail'])->name('fetch.email');
    Route::post('/admin-update-emails', [EmailsController::class, 'updateEmail'])->name('update.emails');
    Route::get('/get-updated-emails-table', [EmailsController::class, 'getUpdatedEmailsTable'])->name('get.updated.emails.table');
    Route::get('/admin-search-email', [EmailsController::class, 'searchEmails'])->name('admin.search.email');
    Route::delete('/admin-delete-emails', [EmailsController::class, 'deleteEmail'])->name('delete.emails');

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
    Route::get('/get-updated-function-rooms-table', [FunctionRoomsController::class, 'getUpdatedFunctionRoomsTable'])->name('get.updated.function.rooms.table');

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

});
