<?php

use App\Http\Controllers\Admin\ClientClientController;
use App\Http\Controllers\Admin\ClientCommentController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\QuestionConditionController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuestionOptionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionnaireController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/api/countries', [CountryController::class, 'index'])->name('api.countries');
Route::get('/api/countries/{iso2}/cities', [CountryController::class, 'cities'])->name('api.cities');

Route::prefix('questionnaire')->name('questionnaire.')->group(function () {
    Route::get('/', [QuestionnaireController::class, 'index'])->name('index');
    Route::post('/start', [QuestionnaireController::class, 'start'])->middleware('throttle:5,1')->name('start');

    Route::get('/wizard', [QuestionnaireController::class, 'wizard'])->name('wizard');
    Route::post('/wizard', [QuestionnaireController::class, 'submitWizard'])->name('wizard.submit');

    Route::get('/step/{step}', [QuestionnaireController::class, 'show'])->name('step');
    Route::post('/step/{step}', [QuestionnaireController::class, 'store'])->name('step.store');

    Route::get('/previous', [QuestionnaireController::class, 'previous'])->name('previous');

    Route::get('/summary', [QuestionnaireController::class, 'summary'])->name('summary');
    Route::post('/submit', [QuestionnaireController::class, 'submit'])->name('submit');

    Route::post('/file', [QuestionnaireController::class, 'storeFile'])->name('file.store');
    Route::delete('/file/{file}', [QuestionnaireController::class, 'destroyFile'])->name('file.destroy');

    Route::get('/thankyou', [QuestionnaireController::class, 'thankyou'])->name('thankyou');
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

    Route::resource('projects', ProjectController::class)->only(['index', 'show', 'destroy']);
    Route::patch('projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.update-status');

    Route::resource('clients', ClientController::class)->only(['index', 'show']);
    Route::patch('clients/{client}/convert', [ClientController::class, 'convert'])->name('clients.convert');
    Route::post('clients/{client}/comments', [ClientCommentController::class, 'store'])->name('clients.comments.store');
    Route::delete('clients/{client}/comments/{comment}', [ClientCommentController::class, 'destroy'])->name('clients.comments.destroy');

    Route::resource('client-clients', ClientClientController::class)->only(['index', 'show']);

    Route::resource('users', UserController::class)->only(['index', 'store', 'update']);
    Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

    Route::patch('questions/reorder', [QuestionController::class, 'reorder'])->name('questions.reorder');
    Route::patch('questions/{question}/toggle', [QuestionController::class, 'toggleActive'])->name('questions.toggle');
    Route::resource('questions', QuestionController::class)->except(['show']);

    Route::resource('questions/{question}/options', QuestionOptionController::class)->only(['store', 'update', 'destroy']);
    Route::resource('questions/{question}/conditions', QuestionConditionController::class)->only(['store', 'update', 'destroy']);
});

require __DIR__.'/auth.php';
