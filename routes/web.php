<?php

use App\Http\Controllers\Admin\ClientClientController;
use App\Http\Controllers\Admin\ClientCommentController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\CompanySettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\QuestionConditionController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuestionOptionController;
use App\Http\Controllers\Admin\QuoteController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionnaireController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

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

    Route::get('comments', [CommentController::class, 'index'])->name('comments.index');

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

    Route::patch('questions/reorder', [QuestionController::class, 'reorder'])->name('questions.reorder');
    Route::patch('questions/{question}/toggle', [QuestionController::class, 'toggleActive'])->name('questions.toggle');
    Route::resource('questions', QuestionController::class)->except(['show']);

    // Nested resource names would contain the {question} segment
    // (admin.questions.{question}.options.store) while the views call
    // admin.questions.options.* / admin.questions.conditions.* — declared
    // explicitly here so those names exist.
    Route::post('questions/{question}/options', [QuestionOptionController::class, 'store'])->name('questions.options.store');
    Route::patch('questions/{question}/options/{option}', [QuestionOptionController::class, 'update'])->name('questions.options.update');
    Route::delete('questions/{question}/options/{option}', [QuestionOptionController::class, 'destroy'])->name('questions.options.destroy');
    Route::post('questions/{question}/conditions', [QuestionConditionController::class, 'store'])->name('questions.conditions.store');
    Route::patch('questions/{question}/conditions/{condition}', [QuestionConditionController::class, 'update'])->name('questions.conditions.update');
    Route::delete('questions/{question}/conditions/{condition}', [QuestionConditionController::class, 'destroy'])->name('questions.conditions.destroy');
});
