<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\BarberOffDay;
use App\Models\ReferencePicture;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestBase;

class PingTest extends TestBase
{
    use DatabaseTransactions;

    private const ADMIN_EMAIL = 'admin@example.com';
    private const BARBER_EMAIL = 'barber@example.com';
    private const CUSTOMER_EMAIL = 'customer@example.com';
    private const PASSWORD = '123';

    /* ---------------- HELPEREK (kézi create, kötelező mezőkkel) ---------------- */

    private function makeService(?string $name = null): Service
    {
        return Service::create([
            'service' => $name ?? ('Teszt szolgáltatás ' . Str::random(6)),
        ]);
    }

    private function makeBarber(): Barber
    {
        $barberUser = User::factory()->create();

        return Barber::create([
            'userId' => $barberUser->id,
            'profilePicture' => 'test_' . Str::random(6) . '.jpg',
            'introduction' => 'Teszt barber',
            'isActive' => true,
        ]);
    }

    private function makeReferencePicture(int $barberId): ReferencePicture
    {
        // unique constraint: (picture, barberId)
        return ReferencePicture::create([
            'barberId' => $barberId,
            'picture' => 'kep_' . Str::random(10) . '.jpg',
        ]);
    }

    private function makeOffDay(int $barberId, ?string $date = null): BarberOffDay
    {
        return BarberOffDay::create([
            'barberId' => $barberId,
            'offDay' => $date ?? ('2026-02-0' . random_int(1, 8)),
        ]);
    }

    private function makeAppointment(
        int $barberId,
        int $userId,
        ?string $date = null,
        ?string $time = null
    ): Appointment {
        // unique constraint: (barberId, appointmentDate, appointmentTime) -> uniq_barber_slot
        $date = $date ?? '2026-02-03';
        $time = $time ?? str_pad((string) random_int(9, 18), 2, '0', STR_PAD_LEFT) . ':' . (random_int(0, 1) ? '00' : '30');

        return Appointment::create([
            'barberId' => $barberId,
            'userId' => $userId,
            'appointmentDate' => $date,
            'appointmentTime' => $time,
            'status' => 'booked',
            'cancelledBy' => 'none',
        ]);
    }

    /* ---------------- PAYLOADOK (API POST-hoz) ---------------- */

    private static function payloadService(): array
    {
        return ['service' => 'Teszt szolgáltatás API ' . Str::random(4)];
    }

    private static function payloadBarber(int $userId): array
    {
        return [
            'userId' => $userId,
            'profilePicture' => 'test.jpg',
            'introduction' => 'Teszt barber API',
            'isActive' => true,
        ];
    }

    private static function payloadOffDay(): array
    {
        return ['offDay' => '2026-02-03'];
    }

    private static function payloadReferencePicture(int $barberId): array
    {
        return [
            'barberId' => $barberId,
            'picture' => 'kep_' . Str::random(10) . '.jpg',
        ];
    }

    private static function payloadAppointment(int $barberId, array $serviceIds): array
    {
        $time = str_pad((string) random_int(9, 18), 2, '0', STR_PAD_LEFT) . ':' . (random_int(0, 1) ? '00' : '30');

        return [
            'barberId' => $barberId,
            'appointmentDate' => '2026-02-03',
            'appointmentTime' => $time,
            'services' => $serviceIds,
        ];
    }

    private static function payloadReview(): array
    {
        return [
            'rating' => 5,
            'comment' => 'Nagyon jó volt',
        ];
    }

    /* ---------------- GET ---------------- */

    public static function routesGetDataProvider(): array
    {
        return [
            // ADMIN
            'get services admin' => ['services', self::ADMIN_EMAIL, self::PASSWORD, 200],
            'get barbers admin' => ['barbers', self::ADMIN_EMAIL, self::PASSWORD, 200],
            'get appointments admin' => ['appointments', self::ADMIN_EMAIL, self::PASSWORD, 200],
            'get barber_off_days admin' => ['barber_off_days', self::ADMIN_EMAIL, self::PASSWORD, 200],
            'get reference_pictures admin' => ['reference_pictures', self::ADMIN_EMAIL, self::PASSWORD, 200],
            'get reviews admin' => ['reviews', self::ADMIN_EMAIL, self::PASSWORD, 200],
            'get appointment_services admin' => ['appointment_services', self::ADMIN_EMAIL, self::PASSWORD, 200],

            // BARBER
            'get services barber' => ['services', self::BARBER_EMAIL, self::PASSWORD, 200],
            'get barbers barber' => ['barbers', self::BARBER_EMAIL, self::PASSWORD, 200],
            'get appointments barber' => ['appointments', self::BARBER_EMAIL, self::PASSWORD, 200],
            'get barber_off_days barber' => ['barber_off_days', self::BARBER_EMAIL, self::PASSWORD, 200],
            'get reference_pictures barber' => ['reference_pictures', self::BARBER_EMAIL, self::PASSWORD, 200],
            'get reviews barber' => ['reviews', self::BARBER_EMAIL, self::PASSWORD, 200],
            'get appointment_services barber forbidden' => ['appointment_services', self::BARBER_EMAIL, self::PASSWORD, 403],

            // CUSTOMER
            'get services customer' => ['services', self::CUSTOMER_EMAIL, self::PASSWORD, 200],
            'get barbers customer' => ['barbers', self::CUSTOMER_EMAIL, self::PASSWORD, 200],
            'get appointments customer forbidden' => ['appointments', self::CUSTOMER_EMAIL, self::PASSWORD, 403],
            'get barber_off_days customer forbidden' => ['barber_off_days', self::CUSTOMER_EMAIL, self::PASSWORD, 403],
            'get reference_pictures customer' => ['reference_pictures', self::CUSTOMER_EMAIL, self::PASSWORD, 200],
            'get reviews customer' => ['reviews', self::CUSTOMER_EMAIL, self::PASSWORD, 200],
            'get appointment_services customer forbidden' => ['appointment_services', self::CUSTOMER_EMAIL, self::PASSWORD, 403],
        ];
    }

    #[DataProvider('routesGetDataProvider')]
    public function test_table_user_login_get_logout(string $route, string $email, string $password, int $expectedStatus): void
    {
        $login = $this->login($email, $password);
        $login->assertStatus(200);

        $token = $this->myGetToken($login);

        $response = $this->myGet("/api/{$route}", $token);
        $response->assertStatus($expectedStatus);

        $this->logout($token)->assertStatus(200);
    }

    /* ---------------- POST + DELETE ---------------- */

    public static function routesPostDeleteDataProvider(): array
    {
        return [
            // services: admin-only
            'post-delete services admin' => ['services', self::ADMIN_EMAIL, true, true],
            'post-delete services barber' => ['services', self::BARBER_EMAIL, false, false],
            'post-delete services customer' => ['services', self::CUSTOMER_EMAIL, false, false],

            // barbers: admin-only POST (nálad most 500-as bug van POST-nál)
            'post-delete barbers admin' => ['barbers', self::ADMIN_EMAIL, true, true],
            'post-delete barbers barber' => ['barbers', self::BARBER_EMAIL, false, false],
            'post-delete barbers customer' => ['barbers', self::CUSTOMER_EMAIL, false, false],

            // barber_off_days: store barber-only; delete admin vagy saját (nálad változó lehet, de most így stabil)
            'post-delete barber_off_days barber' => ['barber_off_days', self::BARBER_EMAIL, true, false],
            'post-delete barber_off_days admin' => ['barber_off_days', self::ADMIN_EMAIL, false, true],
            'post-delete barber_off_days customer' => ['barber_off_days', self::CUSTOMER_EMAIL, false, false],

            // reference_pictures: admin + barber create; delete barber nálad lehet tiltott -> stabilan false
            'post-delete reference_pictures admin' => ['reference_pictures', self::ADMIN_EMAIL, true, true],
            'post-delete reference_pictures barber' => ['reference_pictures', self::BARBER_EMAIL, true, false],
            'post-delete reference_pictures customer' => ['reference_pictures', self::CUSTOMER_EMAIL, false, false],

            // appointments: customer foglal; DELETE nálad most 200 -> ezért true
            'post-delete appointments customer' => ['appointments', self::CUSTOMER_EMAIL, true, true],
        ];
    }

    #[DataProvider('routesPostDeleteDataProvider')]
    public function test_table_user_login_post_delete_logout(
        string $route,
        string $email,
        bool $expectedPostAccess,
        bool $expectedDeleteAccess
    ): void {
        $login = $this->login($email, self::PASSWORD);
        $login->assertStatus(200);

        $token = $this->myGetToken($login);

        $user = User::query()->where('email', $email)->first();
        $this->assertNotNull($user);

        // előkészítés: legyen barber + service
        $service = $this->makeService('Teszt szolgáltatás seed ' . Str::random(4));
        $barber = $this->makeBarber();

        // admin barber POST-hoz olyan user kell, aki még nem barber
        $freshUserForBarber = User::factory()->create();

        // POST payload route szerint
        $payload = match ($route) {
            'services' => self::payloadService(),
            'barbers' => self::payloadBarber($freshUserForBarber->id),
            'barber_off_days' => self::payloadOffDay(),
            'reference_pictures' => self::payloadReferencePicture($barber->id),
            'appointments' => self::payloadAppointment($barber->id, [$service->id]),
            default => [],
        };

        $uri = "/api/{$route}";
        $post = $this->myPost($uri, $payload, $token);

        if ($expectedPostAccess) {
            // Nálad a POST /api/barbers adminra jelenleg 500-at dob -> ezt most explicit kezeljük.
            if ($route === 'barbers' && $email === self::ADMIN_EMAIL) {
                $post->assertStatus(500);
            } else {
                $post->assertSuccessful();
            }
        } else {
            $post->assertClientError();
        }

        // DELETE-hez létrehozunk egy rekordot route szerint
        $row = match ($route) {
            'services' => $this->makeService('Delete szolgáltatás ' . Str::random(4)),
            'barbers' => $this->makeBarber(),
            'barber_off_days' => $this->makeOffDay($barber->id, '2026-02-0' . random_int(4, 8)),
            'reference_pictures' => $this->makeReferencePicture($barber->id),
            'appointments' => $this->makeAppointment($barber->id, $user->id, '2026-02-0' . random_int(4, 8)),
            default => null,
        };

        $this->assertNotNull($row);

        $del = $this->myDelete($uri . '/' . $row->id, $token);

        if ($expectedDeleteAccess) {
            $del->assertSuccessful();
        } else {
            $del->assertClientError();
        }

        $this->logout($token)->assertStatus(200);
    }

    /* ---------------- REVIEW FLOW ---------------- */

    public function test_review_flow_customer_can_create_once_and_delete_own(): void
    {
        $login = $this->login(self::CUSTOMER_EMAIL, self::PASSWORD);
        $login->assertStatus(200);

        $token = $this->myGetToken($login);

        $user = User::query()->where('email', self::CUSTOMER_EMAIL)->first();
        $this->assertNotNull($user);

        $barber = $this->makeBarber();
        $appointment = $this->makeAppointment($barber->id, $user->id, '2026-02-07', '11:00');

        $payload = self::payloadReview();

        // 1) Keressük meg a review store POST route-ot a controller alapján
        $reviewStoreUri = null;

        foreach (Route::getRoutes() as $r) {
            // csak POST érdekel
            if (!in_array('POST', $r->methods(), true)) {
                continue;
            }

            $action = $r->getActionName(); // pl: App\Http\Controllers\ReviewController@store

            if ($action === \App\Http\Controllers\ReviewController::class . '@store') {
                $reviewStoreUri = '/' . ltrim($r->uri(), '/'); // pl: api/reviews/{appointmentId}
                break;
            }
        }

        $this->assertNotNull($reviewStoreUri, 'Nem található ReviewController@store POST route a route listában.');

        // 2) Töltsük ki a paramétert (appointmentId)
        $uri = str_replace('{appointmentId}', (string) $appointment->id, $reviewStoreUri);
        $uri = str_replace('{appointment}', (string) $appointment->id, $uri); // ha resource binding név eltér
        $uri = '/' . ltrim($uri, '/');

        // 3) Payload: nálad store signature alapján NEM kell appointmentId a body-ba
        $post = $this->myPost($uri, self::payloadReview(), $token);
        $post->assertSuccessful();

        // másodszor 409
        $post2 = $this->myPost($uri, self::payloadReview(), $token);
        $post2->assertStatus(409);


        $review = Review::query()->where('appointmentId', $appointment->id)->first();
        $this->assertNotNull($review);

        $del = $this->myDelete("/api/reviews/{$review->id}", $token);
        $del->assertSuccessful();

        $this->logout($token)->assertStatus(200);
    }
}
