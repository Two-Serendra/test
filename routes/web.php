<?php

use App\Http\Controllers\Backend\ServicesController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\UserActionsController;
use App\Http\Controllers\Frontend\UserWorkPermitController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Frontend\NavbarController;
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
    return view('auth.login');
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
Route::get('/dowbloadables', [NavbarController::class, 'downloadables'])->name('downloadables');
Route::get('/sections', [NavbarController::class, 'sections'])->name('sections');
Route::get('/maps', [NavbarController::class, 'maps'])->name('maps');
Route::get('/events', [NavbarController::class, 'events'])->name('events');
Route::get('/events/{id}', [NavbarController::class, 'showEventDetails'])->name('show.event.details');



Route::get('/our-team', [NavbarController::class, 'ourTeam'])->name('ourTeam');
Route::get('/gallery', [NavbarController::class, 'gallery'])->name('gallery');

Route::get('/minor-work-permit', [NavbarController::class, 'minorWorkPermit'])->middleware(['auth'])->name('minor.work.permit');

//Work Permit
Route::post('/submit-minor-work-permit', [UserWorkPermitController::class, 'submitMinorWorkPermit'])->middleware(['auth'])->name('submit.minor.work.permit');
Route::get('/downloads', [NavbarController::class, 'getAllDownloads'])->name('downloads');

// Route::get('/downloads', [NavbarController::class, 'getAllDownloads'])->middleware(['auth'])->name('downloads');

Route::post('/contact/send', [ContactController::class, 'send'])->name('contact.send');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware('web')
    ->prefix('admin')
    ->group(base_path('routes/admin.php'));

require __DIR__ . '/auth.php';
