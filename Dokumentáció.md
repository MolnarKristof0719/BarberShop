# Dokumentáció – BarberShop

## A szoftver célja
A BarberShop egy fodrászat számára készült időpontfoglaló rendszer. A vendégek online tudnak időpontot foglalni, a fodrászok kezelni tudják saját foglalásaikat és referencia képeiket, az admin pedig a teljes rendszert felügyeli (felhasználók, fodrászok, szolgáltatások, értékelések, szabadnapok).

## Használatának rövid bemutatása
Az alábbi képernyőképek a felhasználói folyamatokat mutatják be. A képek helye a repo gyökerében lévő `DokumnetacioKepek` mappa.

Képernyőképek javasolt listája:
- `DokumnetacioKepek/01_home.png` – Főoldal
- `DokumnetacioKepek/02_login.png` – Bejelentkezés
- `DokumnetacioKepek/03_services.png` – Szolgáltatások listája
- `DokumnetacioKepek/04_appointment.png` – Időpontfoglalás
- `DokumnetacioKepek/05_my_appointments.png` – Saját foglalások
- `DokumnetacioKepek/06_admin_users.png` – Admin felhasználókezelés

## Komponensek technikai leírása

### Adatbázis
- Technológia: MySQL
- Diagram: `Diagram.png`
- Biztonsági mentés: `AdatbazisBackup.sql`

Táblák és mezők (röviden):

`users`
- `id`, `name`, `email`, `phoneNumber`, `password`, `role`, `created_at`, `updated_at`
- `role`: 1=admin, 2=barber, 3=customer

`barbers`
- `id`, `userId`, `profilePicture`, `introduction`, `isActive`

`services`
- `id`, `service`, `price`

`appointments`
- `id`, `barberId`, `userId`, `appointmentDate`, `appointmentTime`, `totalPrice`, `status`, `cancelledBy`
- egy barber egy időpontra csak egyszer foglalható (`uniq_barber_slot`)

`appointment_services`
- `id`, `appointmentId`, `serviceId` (kapcsolótábla)

`barber_off_days`
- `id`, `barberId`, `offDay`

`reference_pictures`
- `id`, `picture`, `barberId`, `created_at`, `updated_at`

`reviews`
- `id`, `appointmentId`, `barberId`, `userId`, `rating`, `comment`

### Backend
- Technológia: Laravel 12, PHP 8.2, Laravel Sanctum
- Fő belépési pont: `server/routes/api.php`

#### Migráció (mintakód)
`server/database/migrations/2026_01_16_100000_create_services_table.php`
```php
Schema::create('services', function (Blueprint $table) {
    $table->id();
    $table->string('service', 50)->unique();
    $table->integer('price');
});
```

#### Seeder
A seedelés CSV forrásból is történik. A forrásfájlok a `server/database/csv` mappában vannak.

`server/database/seeders/ServiceSeeder.php`
```php
$fileName = 'csv/services.csv';
$delimiter = ';';
$data = CsvReader::csvToArray($fileName, $delimiter);
Service::factory()->createMany($data);
```

#### Endpointok (minták)
Az API REST elven működik, az útvonalak a `server/routes/api.php` fájlban vannak.

Példák:
- `POST /api/users/login` – bejelentkezés
- `POST /api/users` – regisztráció
- `GET /api/services` – szolgáltatások listája
- `POST /api/appointments` – időpont foglalás
- `DELETE /api/appointments/{id}` – időpont lemondás
- `GET /api/barbers` – fodrászok listája

#### Minta kontroller
`server/app/Http/Controllers/ServiceController.php`
```php
public function store(StoreServiceRequest $request)
{
    return $this->apiResponse(function () use ($request) {
        $this->authorizeAdmin();
        $data = $request->validated();
        return CurrentModel::create($data);
    });
}
```

#### Minta validáció (422)
`server/app/Http/Requests/StoreServiceRequest.php`
```php
public function rules(): array
{
    return [
        'service' => ['required', 'string', 'max:50', Rule::unique('services', 'service')],
        'price' => ['required', 'integer', 'min:0'],
    ];
}
```
A hibák 422-es státusszal és részletes JSON válasszal térnek vissza.

#### Autentikáció
- Laravel Sanctum token alapú autentikáció
- A tokenhez szerepkörönként eltérő jogosultságok (abilities) tartoznak
- Szerepkörök: admin (1), barber (2), customer (3)
- Bejelentkezés után a kliens a tokent `Authorization: Bearer <token>` formában küldi

### Frontend
- Technológia: Vue 3 + Vite + Pinia + Axios + Bootstrap
- Belépési pont: `client/src/main.js`
- Fő komponens: `client/src/App.vue`

#### Oldal szerkezet
- `client/src/router/index.js` – oldalak, route-ok, jogosultság kezelés
- `client/src/views` – oldalak
- `client/src/components` – újrahasznosítható UI elemek

#### Jogosultsági rendszer kezelése
- Backend szinten: Sanctum token + abilities
- Menü szinten: a megjelenítés role alapján történik
- Route szinten: a router `meta.roles` alapján enged be

#### Kliens oldali mappa-szerkezet
- `client/src/api` – Axios API hívások
- `client/src/stores` – Pinia store-ok (auth, szolgáltatások, foglalások, stb.)
- `client/src/views` – oldalak
- `client/src/components` – komponensek

#### Példa API szolgáltatás
`client/src/api/serviceService.js`
```js
import axiosClient from "./axiosClient";

export default {
  getAll() {
    return axiosClient.get("/services");
  },
};
```

#### Reszponzivitás
A felület Bootstrap segítségével reszponzív. Mobilon a rácsos elrendezés és a menü is alkalmazkodik a kisebb kijelzőkhöz.

## Forráslista
- Laravel dokumentáció
- Vue.js dokumentáció
- Bootstrap dokumentáció
- School_2026 mintaprojekt (frontend mintafeladat)
