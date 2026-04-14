# README – Műszaki feltételek és telepítés

## Fejlesztési környezethez szükséges szoftverek
- Git
- PHP 8.2
- Composer
- Node.js (ajánlott: 20.19.0 vagy újabb)
- MySQL

## Forráskód letöltése
1. Klónozd a repót.
2. Lépj be a projekt gyökerébe, ahol a `client` és `server` mappák vannak.

## Backend telepítés és futtatás
1. Lépj a `server` mappába.
2. Telepítsd a PHP csomagokat.
3. Másold az `.env.example` fájlt `.env` néven, és állítsd be az adatbázis kapcsolatot.
4. Generálj alkalmazás kulcsot.
5. Futtasd a migrációkat és seedereket.
6. Indítsd el a fejlesztői szervert.

Parancsok:
```console
cd server
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

## Frontend telepítés és futtatás
1. Lépj a `client` mappába.
2. Telepítsd a csomagokat.
3. Indítsd el a fejlesztői szervert.

Parancsok:
```console
cd client
npm install
npm run dev
```

## Teszt környezet futtatása
Backend:
```console
cd server
php artisan test
```

Frontend (unit):
```console
cd client
npm run test:unit
```

Frontend (e2e):
```console
cd client
npm run test:e2e
```
