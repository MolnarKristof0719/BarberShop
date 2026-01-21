<?php

use App\Http\Controllers\AppointmentServiceController;
use App\Http\Controllers\ReferencePictureController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\BarberOffDayController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ReviewController;

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
Route::get('/barbers', [BarberController::class, 'index']);
Route::get('/barbers/{id}', [BarberController::class, 'show']);
Route::get('/services', [ServiceController::class, 'index']);

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


//region Public Data
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/barbers', [BarberController::class, 'index']);

//endregion

Route::middleware('auth:sanctum')->group(function () {

    //region Auth User
    Route::get('/me', function (Request $request) {
        return $request->user();
    });
    //endregion

    //region Admin
    Route::middleware('isAdmin')->group(function () {
        Route::post('/services', [ServiceController::class, 'store']);
        Route::put('/services/{id}', [ServiceController::class, 'update']);
        Route::delete('/services/{id}', [ServiceController::class, 'destroy']);

        Route::post('/barbers', [BarberController::class, 'store']);
        Route::put('/barbers/{id}', [BarberController::class, 'update']);
        Route::delete('/barbers/{id}', [BarberController::class, 'destroy']);
    });
    //endregion

    //region Barber
    Route::middleware('isBarber')->group(function () {
        Route::get('/off-days', [BarberOffDayController::class, 'index']);
        Route::post('/off-days', [BarberOffDayController::class, 'store']);
        Route::delete('/off-days/{id}', [BarberOffDayController::class, 'destroy']);
    });
    //endregion

    //region Appointments
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
    Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']);
    //endregion

    //region Appointment Services
    Route::get('/appointment_services', [AppointmentServiceController::class, 'index']);
    Route::get('/appointment_services/{id}', [AppointmentServiceController::class, 'show']);
    //endregion

    //region Reviews
    Route::post(
        '/appointments/{appointmentId}/review',
        [ReviewController::class, 'store']
    );
    //endregion

    //region Reference Pictures
    Route::post('/reference_pictures', [ReferencePictureController::class, 'store']);
    Route::put('/reference_pictures/{id}', [ReferencePictureController::class, 'update']);
    Route::delete('/reference_pictures/{id}', [ReferencePictureController::class, 'destroy']);
    //endregion
});

