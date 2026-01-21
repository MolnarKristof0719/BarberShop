<?php

use App\Http\Controllers\AppointmentServiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReferencePictureController;
use App\Http\Controllers\UserController;
use Database\Seeders\ServiceSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

//endpoint
Route::get('/x', function () {
    return 'API';
});


//region users
//User kezelés, login, logout
//Mindenki
Route::post('users/login', [UserController::class, 'login']);
Route::post('users/logout', [UserController::class, 'logout']);
Route::post('users', [UserController::class, 'store']);

//Admin: 
//minden user lekérdezése
Route::get('users', [UserController::class, 'index'])
    ->middleware('auth:sanctum', 'ability:admin');
//Egy user lekérése    
Route::get('users/{id}', [UserController::class, 'show'])
    ->middleware('auth:sanctum', 'ability:admin');
//User adatok módosítása      
Route::patch('users/{id}', [UserController::class, 'update'])
    ->middleware('auth:sanctum', 'ability:admin');
//User törlés
Route::delete('users/{id}', [UserController::class, 'destroy'])
    ->middleware('auth:sanctum', 'ability:admin');

//User self (Amit a user önmagával csinálhat) parancsok
Route::delete('usersme', [UserController::class, 'destroySelf'])
    ->middleware('auth:sanctum', 'ability:usersme:delete');

Route::patch('usersme', [UserController::class, 'updateSelf'])
    ->middleware('auth:sanctum', 'ability:usersme:patch');

Route::patch('usersmeupdatepassword', [UserController::class, 'updatePassword'])
    ->middleware('auth:sanctum', 'ability:usersme:updatePassword');

Route::get('usersme', [UserController::class, 'indexSelf'])
    ->middleware('auth:sanctum', 'ability:usersme:get');
//endregion

//region Services
Route::get('services', [ServiceSeeder::class, 'index']);
Route::get('services/{id}', [ServiceSeeder::class, 'show']);
Route::post('services', [ServiceSeeder::class, 'store'])
    ->middleware(['auth:sanctum', 'ability:services:post']);
Route::patch('services/{id}', [ServiceSeeder::class, 'update'])
    ->middleware(['auth:sanctum', 'ability:services:patch']);
Route::delete('services/{id}', [ServiceSeeder::class, 'destroy'])
    ->middleware(['auth:sanctum', 'ability:services:delete']);
//endregion

//region Barbers
Route::get('barbers', [BarberController::class, 'index']);
Route::get('barbers/{id}', [BarberController::class, 'show']);
Route::post('barbers', [BarberController::class, 'store'])
    ->middleware(['auth:sanctum', 'ability:barbers:post']);
Route::patch('barbers/{id}', [BarberController::class, 'update'])
    ->middleware(['auth:sanctum', 'ability:barbers:patch']);
Route::delete('barbers/{id}', [BarberController::class, 'destroy'])
    ->middleware(['auth:sanctum', 'ability:barbers:delete']);
//endregion

//region Appointments
Route::get('appointments', [AppointmentController::class, 'index']);
Route::get('appointments/{id}', [AppointmentController::class, 'show']);
Route::post('appointments', [AppointmentController::class, 'store'])
    ->middleware(['auth:sanctum', 'ability:appointments:post']);
Route::patch('appointments/{id}', [AppointmentController::class, 'update'])
    ->middleware(['auth:sanctum', 'ability:appointments:patch']);
Route::delete('appointments/{id}', [AppointmentController::class, 'destroy'])
    ->middleware(['auth:sanctum', 'ability:appointments:delete']);
//endregion

//region AppointmentServices 
Route::get('appointment_services', [AppointmentServiceController::class, 'index']);
Route::get('appointment_services/{id}', [AppointmentServiceController::class, 'show']);
Route::post('appointment_services', [AppointmentServiceController::class, 'store'])
    ->middleware(['auth:sanctum', 'ability:appointment_services:post']);
Route::patch('appointment_services/{id}', [AppointmentServiceController::class, 'update'])
    ->middleware(['auth:sanctum', 'ability:appointment_services:patch']);
Route::delete('appointment_services/{id}', [AppointmentServiceController::class, 'destroy'])
    ->middleware(['auth:sanctum', 'ability:appointment_services:delete']);
//endregion

//region BarberOffDays
Route::get('barber_off_days', [BarberOffDayController::class, 'index']);
Route::get('barber_off_days/{id}', [BarberOffDayController::class, 'show']);
Route::post('barber_off_days', [BarberOffDayController::class, 'store'])
    ->middleware(['auth:sanctum', 'ability:barber_off_days:post']);
Route::patch('barber_off_days/{id}', [BarberOffDayController::class, 'update'])
    ->middleware(['auth:sanctum', 'ability:barber_off_days:patch']);
Route::delete('barber_off_days/{id}', [BarberOffDayController::class, 'destroy'])
    ->middleware(['auth:sanctum', 'ability:barber_off_days:delete']);
//endregion

//region ReferencePictures
Route::get('reference_pictures', [ReferencePictureController::class, 'index']);
Route::get('reference_pictures/{id}', [ReferencePictureController::class, 'show']);
Route::post('reference_pictures', [ReferencePictureController::class, 'store'])
    ->middleware(['auth:sanctum', 'ability:reference_pictures:post']);
Route::patch('reference_pictures/{id}', [ReferencePictureController::class, 'update'])
    ->middleware(['auth:sanctum', 'ability:reference_pictures:patch']);
Route::delete('reference_pictures/{id}', [ReferencePictureController::class, 'destroy'])
    ->middleware(['auth:sanctum', 'ability:reference_pictures:delete']);
//endregion

//region Review
Route::get('reviews', [ReviewController::class, 'index']);
Route::get('reviews/{id}', [ReviewController::class, 'show']);
Route::post('reviews', [ReviewController::class, 'store'])
    ->middleware(['auth:sanctum', 'ability:reviews:post']);
Route::patch('reviews/{id}', [ReviewController::class, 'update'])
    ->middleware(['auth:sanctum', 'ability:reviews:patch']);
Route::delete('reviews/{id}', [ReviewController::class, 'destroy'])
    ->middleware(['auth:sanctum', 'ability:reviews:delete']);
//endregion