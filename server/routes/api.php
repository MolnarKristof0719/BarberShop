<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\BarberOffDayController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReferencePictureController;
use App\Http\Controllers\AppointmentServiceController;

Route::get('/x', fn() => 'API');

// --- PUBLIC ---
Route::post('users/login', [UserController::class, 'login']);
Route::post('users', [UserController::class, 'store']); // regisztráció

Route::get('/services', [ServiceController::class, 'index']); // public
Route::get('/servicessortsearch/{column}/{direction}/{search?}', [ServiceController::class, 'indexSortSearch']);

// Ha nálatok a barberek listája PUBLIC legyen, hagyd így:
// Route::get('/barbers', [BarberController::class, 'index']);
// Route::get('/barbers/{id}', [BarberController::class, 'show']);

// Ha NEM public, akkor csak auth + ability:barbers:get alatt lesz (lent).


Route::middleware('auth:sanctum')->group(function () {

    // --- COMMON AUTH ---
    Route::post('users/logout', [UserController::class, 'logout']);

    Route::get('/me', function (Request $request) {
        return $request->user();
    });

    // --- USERSME (mindenki, akinek usersme:* ability megvan) ---
    Route::middleware('ability:usersme:get')->get('usersme', [UserController::class, 'indexSelf']);
    Route::middleware('ability:usersme:patch')->patch('usersme', [UserController::class, 'updateSelf']);
    Route::middleware('ability:usersme:updatePassword')->patch('usersmeupdatepassword', [UserController::class, 'updatePassword']);
    Route::middleware('ability:usersme:delete')->delete('usersme', [UserController::class, 'destroySelf']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('usersme/appointments', [UserController::class, 'myAppointments'])
            ->middleware('ability:usersme:get');

        Route::get('usersme/appointments/{id}', [UserController::class, 'myAppointmentShow'])
            ->middleware('ability:usersme:get');

        Route::delete('usersme/appointments/{id}', [UserController::class, 'myAppointmentCancel'])
            ->middleware('ability:appointments:delete');
    });

    // --- ADMIN USERS CRUD ---
    Route::get('users', [UserController::class, 'index'])->middleware('ability:*');
    Route::get('users/{id}', [UserController::class, 'show'])->middleware('ability:*');
    Route::patch('users/{id}', [UserController::class, 'update'])->middleware('ability:*');
    Route::delete('users/{id}', [UserController::class, 'destroy'])->middleware('ability:*');

    // --- SERVICES ADMIN CRUD (ha kell adminnak) ---
    Route::post('/services', [ServiceController::class, 'store'])->middleware('ability:*');
    Route::put('/services/{id}', [ServiceController::class, 'update'])->middleware('ability:*');
    Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->middleware('ability:*');

    // --- BARBERS (ability:barbers:get) ---
    Route::get('/barbers', [BarberController::class, 'index'])->middleware('ability:barbers:get');
    Route::get('/barberssortsearch/{column}/{direction}/{search?}', [BarberController::class, 'indexSortSearch'])->middleware('ability:barbers:get');
    Route::get('/barbers/{id}', [BarberController::class, 'show'])->middleware('ability:barbers:get');
    Route::get('barberbyid/{barberId}/{column}/{direction}/{search?}', [BarberController::class, 'indexBarbersById']);

    // Admin barber CRUD (ha van ilyen controller funkció)
    Route::post('/barbers', [BarberController::class, 'store'])->middleware('ability:*');
    Route::patch('/barbers/{id}', [BarberController::class, 'update'])->middleware('ability:*');
    Route::put('/barbers/{id}', [BarberController::class, 'update'])->middleware('ability:*');
    Route::delete('/barbers/{id}', [BarberController::class, 'destroy'])->middleware('ability:*');

    // --- OFF DAYS (csak barber, abilities alapján) ---
    Route::get('/barber_off_days', [BarberOffDayController::class, 'index'])->middleware('ability:barber_off_days:get');
    Route::post('/barber_off_days', [BarberOffDayController::class, 'store'])->middleware('ability:barber_off_days:post');
    Route::delete('/barber_off_days/{id}', [BarberOffDayController::class, 'destroy'])->middleware('ability:barber_off_days:delete');

    // --- REFERENCE PICTURES (csak barber, abilities alapján) ---
    Route::get('/reference_pictures', [ReferencePictureController::class, 'index'])->middleware('ability:reference_pictures:get');
    Route::post('/reference_pictures', [ReferencePictureController::class, 'store'])->middleware('ability:reference_pictures:post');
    Route::delete('/reference_pictures/{id}', [ReferencePictureController::class, 'destroy'])->middleware('ability:reference_pictures:delete');

    // --- APPOINTMENTS ---
    // Nálad customer kap: appointments:post és appointments:delete
    // GET-hez NINCS ability a loginban -> vagy public, vagy add hozzá: appointments:get
    Route::get('/appointments', [AppointmentController::class, 'index'])->middleware('ability:appointments:get'); // vagy 'appointments:get' ha bevezeted
    Route::get('/appointments/{id}', [AppointmentController::class, 'show'])->middleware('ability:appointments:show'); // vagy 'appointments:get'

    Route::post('/appointments', [AppointmentController::class, 'store'])->middleware('ability:appointments:post');
    Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy'])->middleware('ability:appointments:delete');

    // --- REVIEWS ---
    // Customer jelenleg nem kap reviews:create ability-t a te loginodban!
    // Ha akarsz review-t, add hozzá a customer abilities listához: 'reviews:post'
    Route::post('/appointments/{appointmentId}/review', [ReviewController::class, 'store'])->middleware('ability:reviews:post');

    Route::get('/reviews', [ReviewController::class, 'index'])->middleware('ability:reviews:get');
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->middleware('ability:reviews:delete');

    // --- PIVOT DEBUG (admin) ---
    Route::get('/appointment_services', [AppointmentServiceController::class, 'index'])->middleware('ability:*');
    Route::get('/appointment_services/{id}', [AppointmentServiceController::class, 'show'])->middleware('ability:*');
});


