# Dokumentáció – BarberShop

## A szoftver célja
A BarberShop egy fodrászat számára készült időpontfoglaló rendszer. A vendégek online tudnak időpontot foglalni, a fodrászok kezelni tudják saját foglalásaikat és referencia képeiket, az admin pedig a teljes rendszert felügyeli. A cél az időpontkezelés digitalizálása, az átlátható szolgáltatáslista, valamint a gyors, hibamentes ügyfélkiszolgálás támogatása.

## Komponensek technikai leírása

### Adatbázis
- Technológia: MySQL
- Diagram: `Diagram.png`
- Biztonsági mentés: `AdatbazisBackup.sql`
- Import: a teljes adatbázis a `AdatbazisBackup.sql` fájlból visszaállítható

Táblák és mezők (röviden):
- `users` – felhasználók és szerepkörök
- Mezők: `id`, `name`, `email`, `phoneNumber`, `password`, `role`, `created_at`, `updated_at`
- Szerepkörök: `role` = 1 (admin), 2 (barber), 3 (customer)

- `barbers` – fodrász profilok
- Mezők: `id`, `userId`, `profilePicture`, `introduction`, `isActive`

- `services` – szolgáltatások és árak
- Mezők: `id`, `service`, `price`

- `appointments` – időpontfoglalások
- Mezők: `id`, `barberId`, `userId`, `appointmentDate`, `appointmentTime`, `totalPrice`, `status`, `cancelledBy`
- Korlát: egy barber egy időpontra csak egyszer foglalható (`uniq_barber_slot`)

- `appointment_services` – kapcsolótábla foglalás és szolgáltatások között
- Mezők: `id`, `appointmentId`, `serviceId`

- `barber_off_days` – fodrász szabadnapok
- Mezők: `id`, `barberId`, `offDay`

- `reference_pictures` – referencia képek
- Mezők: `id`, `picture`, `barberId`, `created_at`, `updated_at`

- `reviews` – értékelések
- Mezők: `id`, `appointmentId`, `barberId`, `userId`, `rating`, `comment`

### Backend
- Technológia: Laravel 12, PHP 8.2, Laravel Sanctum
- Fő belépési pont: `server/routes/api.php`
- API stílus: REST, JSON válaszok

Telepítés és futtatás:
```console
cd server
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

Munkához használt Laravel parancsok:
- `php artisan key:generate`
- `php artisan migrate`
- `php artisan db:seed`
- `php artisan serve`
- `php artisan test`

#### Migráció
Példa migráció, amely létrehozza a szolgáltatások táblát. A migrációk felelnek az adatbázis séma verziózott kialakításáért.

`server/database/migrations/2026_01_16_100000_create_services_table.php`
```php
Schema::create('services', function (Blueprint $table) {
    $table->id();
    $table->string('service', 50)->unique();
    $table->integer('price');
});
```

#### Seeder
A seedelés a `server/database/seeders` mappában található. A fő vezérlő a `DatabaseSeeder`, amely sorban meghívja a többi seedert.

Adatforrások:
- `server/database/csv/services.csv` – szolgáltatások listája
- Egyéb seederek – mintaadatok a teszteléshez

Seeder mintakód:
`server/database/seeders/ServiceSeeder.php`
```php
$fileName = 'csv/services.csv';
$delimiter = ';';
$data = CsvReader::csvToArray($fileName, $delimiter);
Service::factory()->createMany($data);
```

#### Endpointok
Az endpointok a `server/routes/api.php` fájlban vannak. A védett útvonalak `auth:sanctum` middleware-rel és `ability:*` jogosultsági ellenőrzéssel futnak.

Minta endpointok:
- `POST /api/users/login` – bejelentkezés
- `POST /api/users` – regisztráció
- `GET /api/services` – szolgáltatások listája
- `POST /api/appointments` – időpont foglalás
- `DELETE /api/appointments/{id}` – időpont lemondás
- `GET /api/barbers` – fodrászok listája

Minta kontroller:
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

Minta modell:
`server/app/Models/Service.php`
```php
class Service extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['service', 'price'];

    public function appointments()
    {
        return $this->belongsToMany(
            Appointment::class,
            'appointment_services',
            'serviceId',
            'appointmentId'
        );
    }
}
```

Minta validáció (422):
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
Hibás kérés esetén a backend 422-es státuszkóddal és részletes JSON hibával válaszol.

#### Autentikáció
- Bejelentkezés: `POST /api/users/login`
- Kijelentkezés: `POST /api/users/logout`
- Token alapú azonosítás: `Authorization: Bearer <token>`
- Szerepkörök: admin, barber, customer
- Jogosultságok: role alapján kiosztott `abilities`, amelyek védik az endpointokat

### Frontend
- Technológia: Vue 3 + Vite + Pinia + Axios + Bootstrap
- Belépési pont: `client/src/main.js`
- Fő komponens: `client/src/App.vue`

Oldal szerkezet:
- Router: `client/src/router/index.js`
- Oldalak: `client/src/views`
- Komponensek: `client/src/components`

Jogosultsági rendszer kezelése:
- Backend szint: Sanctum token + abilities
- Menü szint: role alapján megjelenített menüpontok
- Route szint: `meta.roles` alapján védett útvonalak

Kliens oldali mappa-szerkezet:
- `client/src/api` – Axios API réteg
- `client/src/stores` – Pinia store-ok
- `client/src/views` – oldalak
- `client/src/components` – újrahasznosítható elemek
- `client/src/router` – routing és jogosultságok

Program szerkezet és UI elemek:
- Kártyák: szolgáltatások, barber profilok, értékelések
- Lapozás: `client/src/components/Pagination/Pagination.vue`
- Űrlapok: bejelentkezés, regisztráció, időpontfoglalás
- Validáció: API hibák megjelenítése 422 válaszok alapján
- Reszponzivitás: Bootstrap rácsrendszer és mobilbarát elrendezés

Példa API szolgáltatás:
`client/src/api/serviceService.js`
```js
import axiosClient from "./axiosClient";

export default {
  getAll() {
    return axiosClient.get("/services");
  },
};
```

## Forráslista
- Laravel dokumentáció
- Vue.js dokumentáció
- Bootstrap dokumentáció
