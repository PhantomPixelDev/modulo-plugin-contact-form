<?php

use Illuminate\Support\Facades\Route;
use Plugins\ContactForm\src\Http\Controllers\ContactFormController;

Route::post('/contact-form/submit', [ContactFormController::class, 'store'])
    ->middleware('throttle:10,1,contact-form:')
    ->name('contact-form.submit');
