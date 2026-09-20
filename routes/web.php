<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\ClientClientController;
use App\Http\Controllers\Admin\ClientCommentController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\CompanySettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\QuoteController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\CommercialAuthenticatedSessionController;
use App\Http\Controllers\Auth\DeveloperAuthenticatedSessionController;
use App\Http\Controllers\Auth\SuperAdminAuthenticatedSessionController;
use App\Http\Controllers\Commercial\DashboardController as CommercialDashboardController;
use App\Http\Controllers\Commercial\ProspectController as CommercialProspectController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\Developer\DashboardController as DeveloperDashboardController;
use App\Http\Controllers\Developer\ProspectController as DeveloperProspectController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionnaireController;
use App\Http\Controllers\SuperAdmin\ActivityController as SuperAdminActivityController;
use App\Http\Controllers\SuperAdmin\CommercialController as SuperAdminCommercialController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\DeveloperController as SuperAdminDeveloperController;
use App\Http\Controllers\SuperAdmin\ProspectController as SuperAdminProspectController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/api/countries', [CountryController::class, 'index'])->name('api.countries');
Route::get('/api/countries/{iso2}/cities', [CountryController::class, 'cities'])->name('api.cities');

/*
|--------------------------------------------------------------------------
| Formulaire de devis (parcours client)
|--------------------------------------------------------------------------
|
| Cinq étapes : coordonnées, type de projet, détail du besoin, hébergement et
| suivi, niveau et délai. La dernière étape produit le devis provisoire.
| Ce parcours remplace l'ancien questionnaire ; celui-ci n'est plus exposé.
|
*/
Route::prefix('questionnaire')->name('questionnaire.')->group(function () {
    Route::get('/', [QuestionnaireController::class, 'index'])->name('index');
    Route::post('/start', [QuestionnaireController::class, 'start'])->middleware('throttle:5,1')->name('start');

    Route::get('/step/{step}', [QuestionnaireController::class, 'show'])->whereNumber('step')->name('step');
    Route::post('/step/{step}', [QuestionnaireController::class, 'store'])->whereNumber('step')->name('step.store');

    Route::get('/quote', [QuestionnaireController::class, 'quote'])->name('quote');
    Route::get('/restart', [QuestionnaireController::class, 'restart'])->name('restart');
});

Route::post('/internal/import', [ImportController::class, 'import'])
    ->withoutMiddleware(PreventRequestForgery::class)
    ->name('internal.import');

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'admin'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('team-spaces', fn () => view('admin.team-spaces'))->name('team-spaces');

    Route::resource('projects', ProjectController::class)->only(['index', 'show', 'destroy']);
    Route::patch('projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.update-status');

    Route::resource('clients', ClientController::class)->only(['index', 'show']);
    Route::patch('clients/{client}/convert', [ClientController::class, 'convert'])->name('clients.convert');
    Route::post('clients/{client}/comments', [ClientCommentController::class, 'store'])->name('clients.comments.store');
    Route::delete('clients/{client}/comments/{comment}', [ClientCommentController::class, 'destroy'])->name('clients.comments.destroy');

    Route::get('comments', [CommentController::class, 'index'])->name('comments.index');

    Route::get('activities', [ActivityController::class, 'index'])->name('activities.index');

    Route::resource('client-clients', ClientClientController::class)->only(['index', 'show']);

    Route::get('quotes', [QuoteController::class, 'index'])->name('quotes.index');
    Route::get('quotes/create', [QuoteController::class, 'create'])->name('quotes.create');
    Route::post('quotes', [QuoteController::class, 'store'])->name('quotes.store');
    Route::get('quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
    Route::get('quotes/{quote}/edit', [QuoteController::class, 'edit'])->name('quotes.edit');
    Route::patch('quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
    Route::delete('quotes/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy');
    Route::get('quotes/{quote}/preview', [QuoteController::class, 'preview'])->name('quotes.preview');
    Route::get('quotes/{quote}/pdf', [QuoteController::class, 'pdf'])->name('quotes.pdf');
    Route::patch('quotes/{quote}/status', [QuoteController::class, 'updateStatus'])->name('quotes.update-status');

    Route::get('company-settings', [CompanySettingController::class, 'edit'])->name('company-settings.edit');
    Route::patch('company-settings', [CompanySettingController::class, 'update'])->name('company-settings.update');

    Route::resource('users', UserController::class)->only(['index', 'store', 'update']);
    Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

});

/*
|--------------------------------------------------------------------------
| Private Super Admin space
|--------------------------------------------------------------------------
|
| Reachable through its own URL only: no entry is added to the general
| dashboard sidebar (config/admin_brand.php stays untouched).
|
*/
Route::get('super-admin/login', [SuperAdminAuthenticatedSessionController::class, 'create'])->middleware('guest')->name('super_admin.login');
Route::post('super-admin/login', [SuperAdminAuthenticatedSessionController::class, 'store'])->middleware('guest');
Route::prefix('super-admin')->name('super_admin.')->middleware('super_admin')->group(function () {
    Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('developers', [SuperAdminDeveloperController::class, 'index'])->name('developers.index');
    Route::post('developers', [SuperAdminDeveloperController::class, 'store'])->name('developers.store');
    Route::get('developers/{developer}', [SuperAdminDeveloperController::class, 'show'])->name('developers.show');

    Route::get('commercials', [SuperAdminCommercialController::class, 'index'])->name('commercials.index');
    Route::post('commercials', [SuperAdminCommercialController::class, 'store'])->name('commercials.store');
    Route::get('commercials/{commercial}', [SuperAdminCommercialController::class, 'show'])->name('commercials.show');

    Route::get('activities', [SuperAdminActivityController::class, 'index'])->name('activities.index');

    Route::get('prospects', [SuperAdminProspectController::class, 'index'])->name('prospects.index');
    Route::get('prospects/{client}', [SuperAdminProspectController::class, 'show'])->name('prospects.show');
    Route::patch('prospects/{client}/assignment', [SuperAdminProspectController::class, 'updateAssignment'])->name('prospects.assignment.update');
});

/*
|--------------------------------------------------------------------------
| Private developer space
|--------------------------------------------------------------------------
|
| Developers sign in with their name on their own login screen and only
| ever see the prospects assigned to them.
|
*/
Route::get('developer/login', [DeveloperAuthenticatedSessionController::class, 'create'])->name('developer.login');
Route::post('developer/login', [DeveloperAuthenticatedSessionController::class, 'store']);

/*
 * The `developer` middleware handles authentication itself so that a guest
 * landing on /developer is sent to the developer login, never to /login.
 */
Route::prefix('developer')->name('developer.')->middleware('developer')->group(function () {
    Route::get('/', [DeveloperDashboardController::class, 'index'])->name('dashboard');

    Route::get('prospects', [DeveloperProspectController::class, 'index'])->name('prospects.index');
    Route::get('prospects/{client}', [DeveloperProspectController::class, 'show'])->name('prospects.show');
    Route::post('prospects/{client}/comments', [DeveloperProspectController::class, 'storeComment'])->name('prospects.comments.store');
    Route::patch('prospects/{client}/follow-up', [DeveloperProspectController::class, 'updateFollowUp'])->name('prospects.follow-up.update');
    Route::patch('prospects/{client}/convert', [DeveloperProspectController::class, 'convert'])->name('prospects.convert');
});

/*
|--------------------------------------------------------------------------
| Private commercial space
|--------------------------------------------------------------------------
|
| Commercials sign in with their name on their own login screen and only
| ever see the prospects assigned to them.
|
*/
Route::get('commercial/login', [CommercialAuthenticatedSessionController::class, 'create'])->name('commercial.login');
Route::post('commercial/login', [CommercialAuthenticatedSessionController::class, 'store']);

Route::prefix('commercial')->name('commercial.')->middleware('commercial')->group(function () {
    Route::get('/', [CommercialDashboardController::class, 'index'])->name('dashboard');

    Route::get('prospects', [CommercialProspectController::class, 'index'])->name('prospects.index');
    Route::get('prospects/{client}', [CommercialProspectController::class, 'show'])->name('prospects.show');
    Route::post('prospects/{client}/comments', [CommercialProspectController::class, 'storeComment'])->name('prospects.comments.store');
    Route::patch('prospects/{client}/follow-up', [CommercialProspectController::class, 'updateFollowUp'])->name('prospects.follow-up.update');
    Route::patch('prospects/{client}/convert', [CommercialProspectController::class, 'convert'])->name('prospects.convert');

    Route::get('converted', [CommercialProspectController::class, 'converted'])->name('converted.index');
});
