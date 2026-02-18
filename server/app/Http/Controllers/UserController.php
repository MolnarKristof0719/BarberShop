<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UpdateUserSelfRequest;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserPasswordRequest;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function login(LoginUserRequest $request)
    {
        //EltĂˇroljuk az adatokat vĂˇltozĂłkba
        $email = $request->input(('email'));
        $password = $request->input(('password'));

        //Az email alapjĂˇn megkeressĂĽk a usert
        $user = User::where('email', $email)->first();

        //Stimmel-e az email Ă©s a jelszĂł?
        if (!$user || !Hash::check($password, $password ? $user->password : '')) {
            return response()->json([
                'message' => 'invalid email or password'
            ], 401);
        }

        //JĂł az email Ă©s a jelszĂł
        //KitĂ¶rĂ¶ljĂĽk az esetleges tokenjeit
        //$user->tokens()->delete();

        //itt adjuk az Ăşj tokent idĹ‘korlĂˇt nĂ©lkĂĽl
        //$user->token = $user->createToken('access')->plainTextToken;

        //LejĂˇrati idĹ‘vel
        // $expirationTime = Carbon::now()->addSeconds(20);
        // $name = "20sec";
        // $expirationTime = Carbon::now()->addMinutes(30);
        // $name ="30min";
        // $expirationTime = Carbon::now()->addHours(4);;
        // $name ="4hours";


        $expirationTime = Carbon::now()->addDays(1);
        $role = $user->role;
        $name = "1day-role:$role";
        switch ($role) {
            case User::ROLE_ADMIN:
                //Admin
                $abilities = ['*'];
                break;
            case User::ROLE_BARBER:
                //Barber
                $abilities = [
                    'usersme:delete',
                    'usersme:patch',
                    'usersme:updatePassword',
                    'usersme:get',
                    'reference_pictures:post',
                    'reference_pictures:delete',
                    'reference_pictures:get',
                    'barber_off_days:get',
                    'barber_off_days:post',
                    'barber_off_days:delete',
                    'appointments:get',
                    'appointments:show',
                    'appointments:post',
                    'appointments:delete',
                    'barbers:get',
                    'reviews:delete',
                    'reviews:get'
                    
                ];
                break;
            default:
                //Customer
                $abilities = [
                    'usersme:delete',
                    'usersme:patch',
                    'usersme:updatePassword',
                    'usersme:get',
                    'barbers:get',
                    'appointments:post',
                    'appointments:delete',
                    'reviews:post',
                    'reviews:get',
                    'reviews:delete',
                    'reference_pictures:get'
                    

                ];
                break;
        }


        $user->token = $user->createToken(
            $name,
            $abilities,
            $expirationTime
        )->plainTextToken;



        //visszaadjuk a usert, ami a tokent is tartalmazni fogja
        $data = [
            'message' => 'ok',
            'data' => $user
        ];
        $status = 200;

        //visszaadjuk a usert, ami a tokent is tartalmazni fogja
        return response()->json($data, $status, options: JSON_UNESCAPED_UNICODE);
    }

    public function logout(Request $request)
    {
        // Minden tokent tĂ¶rĂ¶l (en nem jĂł, mert egy mĂˇsik bejelntkezĂ©st is kivĂ©gez)
        //---------------------
        // // Az $request->user() segĂ­tsĂ©gĂ©vel hozzĂˇfĂ©rĂĽnk a bejelentkezett felhasznĂˇlĂłhoz
        // $user = $request->user();

        // // TĂ¶rĂ¶ljĂĽk a felhasznĂˇlĂł Ă¶sszes tokenjĂ©t
        // $user->tokens()->delete();

        // return response()->json(['message' => 'Successfully logged out']);


        //Egy mĂˇsi mĂłdszer
        // Megkeresi a tokent Ă©s tĂ¶rli ---------------------
        $token = $request->bearerToken(); // Kivonjuk a bearer tokent a kĂ©rĂ©sbĹ‘l

        // MegkeressĂĽk a token modellt
        $personalAccessToken = PersonalAccessToken::findToken($token);

        if ($personalAccessToken) {
            $personalAccessToken->delete();
            $data = [
                'message' => 'ok',
                'data' => []
            ];
        } else {
            $data = [
                'message' => 'Token not found',
                'data' => []
            ];
        }
        return response()->json($data, options: JSON_UNESCAPED_UNICODE);
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            //code...
            $rows = User::all();
            // $sql ="SELECT * FROM products";
            // $rows = DB::select($sql);
            $status = 200;
            $data = [
                'message' => 'OK',
                'data' => $rows
            ];
        } catch (\Exception $e) {
            //throw $th;
            $status = 500;
            $data = [
                'message' => "Server error {$e->getCode()}",
                'data' => $rows
            ];
        }

        return response()->json($data, $status, options: JSON_UNESCAPED_UNICODE);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $row = User::create($request->all());

            $data = [
                'message' => 'ok',
                'data' => $row
            ];
            // Sikeres vĂˇlasz: 201 Created kĂłd ajĂˇnlott Ăşj erĹ‘forrĂˇs lĂ©trehozĂˇsakor
            return response()->json($data, 201, options: JSON_UNESCAPED_UNICODE);
        } catch (QueryException $e) {
            // EllenĹ‘rizzĂĽk, hogy ez egy "Duplicate entry for key" hiba-e (MySQL hibakĂłd: 23000 vagy 1062)
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                $data = [
                    'message' => 'Insert error: The given name already exists, please choose another one',
                    'data' => [
                        'name' => $request->input('name') // VisszakĂĽldhetjĂĽk, mi volt a hibĂˇs
                    ]
                ];
                // Kliens hiba, ami jelzi a kĂ©rĂ©s Ă©rvĂ©nytelensĂ©gĂ©t
                return response()->json($data, 409, options: JSON_UNESCAPED_UNICODE); // 409 Conflict ajĂˇnlott
            }
            // Ha nem ez a hiba volt, dobjuk tovĂˇbb az eredeti kivĂ©telt, vagy kezeljĂĽk mĂˇskĂ©pp
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $row = User::find($id);
        if ($row) {
            # code...
            $status = 200;
            $data = [
                'message' => 'OK',
                'data' => $row
            ];
        } else {
            # code...
            $status = 404;
            $data = [
                'message' => "Not found id: $id",
                'data' => null
            ];
        }
        return response()->json($data, $status, options: JSON_UNESCAPED_UNICODE);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, int $id)
    {
        $row = User::find($id);

        if ($row) {
            # code...
            $status = 200;
            //Szabd-e ezt nekem?
            $userToUpdate = $row;
            $this->authorize('updateAdmin', $userToUpdate);

            $row->update($request->all());

            $data = [
                'message' => 'OK',
                'data' => [
                    'data' => $row
                ]
            ];
        } else {
            # code...
            $status = 404;
            $data = [
                'message' => "Patch error. Not found id: $id",
                'data' => $id
            ];
        }
        return response()->json($data, $status, options: JSON_UNESCAPED_UNICODE);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $row = User::find($id);
        if ($row) {
            # code...
            $status = 200;
            $userToDestroy = $row;
            $this->authorize('deleteAdmin', $userToDestroy);
            $row->delete();

            $data = [
                'message' => 'OK',
                'data' => [
                    'id' => $id
                ]
            ];
        } else {
            # code...
            $status = 404;
            $data = [
                'message' => "Delete error. Not found id: $id",
                'data' => null
            ];
        }
        return response()->json($data, $status, options: JSON_UNESCAPED_UNICODE);
    }

    //Ă–nmagam tĂ¶rlĂ©se
    public function destroySelf(Request $request)
    {
        //KivesszĂĽk a tĂ¶rlendĹ‘ user-t
        $userToDestroy = $request->user();
        // A Policy-t hasznĂˇljuk: 
        $this->authorize('delete', $userToDestroy);
        // ... tĂ¶rlĂ©s logika
        //A user tokenjeinek tĂ¶rlĂ©se
        $userToDestroy->tokens()->delete();
        //A user tĂ¶rlĂ©se
        $userToDestroy->delete();

        $status = 404;
        $data = [
            'message' => "Sikeresen tĂ¶rĂ¶lted a fiĂłkodat",
            'data' => null
        ];
        return response()->json($data, $status, options: JSON_UNESCAPED_UNICODE);
    }


    //Ă–nmagam mĂłdosĂ­tĂˇsa
    public function updateSelf(UpdateUserSelfRequest $request)
    {

        //KivesszĂĽk a mĂłdosĂ­tandĂł user-t
        $userToUpdate = $request->user();
        // A Policy-t hasznĂˇljuk: 
        $this->authorize('update', $userToUpdate);

        $status = 200;
        // $userToUpdate->update($request->all());
        $userToUpdate->update($request->validated());

        $data = [
            'message' => 'OK',
            'data' => [
                'data' => $userToUpdate
            ]
        ];

        return response()->json($data, $status, options: JSON_UNESCAPED_UNICODE);
    }

    //Ă–nmagam jelszavĂˇnak mĂłdosĂ­tĂˇsa
    public function updatePassword(UpdateUserPasswordRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // FrissĂ­tjĂĽk a jelszĂłt (a Laravel 10+ automatikusan hasheli, 
        // ha a model-ben a 'password' mezĹ‘ 'hashed' cast-ot kapott)
        $user->update([
            'password' => Hash::make($request->newpassword)
        ]);

        $data = [
            'message' => 'JelszĂł sikeresen mĂłdosĂ­tva.',
            'data' => [
                'user' => $user
            ]
        ];
        $status = 200;

        return response()->json($data, $status, options: JSON_UNESCAPED_UNICODE);
    }



    //Ă–nmagam megnĂ©zĂ©se
    public function indexSelf(Request $request)
    {
        //KivesszĂĽk a megmutatandĂł usert
        $userToGet = $request->user();
        // A Policy-t hasznĂˇljuk: 
        $this->authorize('view', $userToGet);
        $status = 200;
        $data = [
            'message' => 'OK',
            'data' => [
                'data' => $userToGet
            ]
        ];
        return response()->json($data, $status, options: JSON_UNESCAPED_UNICODE);
    }

    public function myAppointments(Request $request)
    {
        $user = $request->user();

        $rows = Appointment::query()
            ->where('userId', $user->id)
            ->with(['services', 'barber'])
            ->orderByDesc('appointmentDate')
            ->orderByDesc('appointmentTime')
            ->get();

        return response()->json([
            'message' => 'OK',
            'data' => $rows
        ], 200, options: JSON_UNESCAPED_UNICODE);
    }

    public function myAppointmentShow(Request $request, int $id)
    {
        $user = $request->user();

        $row = Appointment::query()
            ->where('id', $id)
            ->where('userId', $user->id)
            ->with(['services', 'barber'])
            ->first();

        if (!$row) {
            return response()->json([
                'message' => "Not found (or not yours) id: $id",
                'data' => null
            ], 404, options: JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'message' => 'OK',
            'data' => $row
        ], 200, options: JSON_UNESCAPED_UNICODE);
    }

    public function myAppointmentCancel(Request $request, int $id)
    {
        $user = $request->user();

        $appointment = Appointment::query()
            ->where('id', $id)
            ->where('userId', $user->id)
            ->first();

        if (!$appointment) {
            return response()->json([
                'message' => "Not found (or not yours) id: $id",
                'data' => null
            ], 404, options: JSON_UNESCAPED_UNICODE);
        }

        if ($appointment->status === 'completed') {
            return response()->json([
                'message' => 'Completed appointment cannot be cancelled.',
                'data' => null
            ], 422, options: JSON_UNESCAPED_UNICODE);
        }

        if ($appointment->status !== 'cancelled') {
            $appointment->update([
                'status' => 'cancelled',
                'cancelledBy' => 'customer',
            ]);
        }

        return response()->json([
            'message' => 'Cancelled',
            'data' => $appointment->fresh()->load(['services', 'barber'])
        ], 200, options: JSON_UNESCAPED_UNICODE);
    }
}
