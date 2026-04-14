# Tesztek

## Kézi tesztek (request.rest)
A `server/request.rest` fájl tartalmazza a kézi API tesztek mintáit.

Példa:
- login
- token kiolvasás
- CRUD műveletek (users, services, appointments)

Futtatás menete (REST Client kiegészítővel):
1. Nyisd meg a `server/request.rest` fájlt.
2. Indítsd el a backend szervert.
3. Végezd el a kéréseket sorban, ellenőrizd a válaszkódokat.

## Backend tesztek
Tesztek helye: `server/tests`

Típusok:
- Unit tesztek: `server/tests/Unit/*`
- Feature tesztek: `server/tests/Feature/*`

Futtatás:
```console
cd server
php artisan test
```

Teszt eredmény dokumentálása:
- A terminál kimenetet mentsd fájlba:
```console
php artisan test > backend-test-results.txt
```
- A fájl helye: repo gyökér (példa: `backend-test-results.txt`)
- Opcionális képernyőkép: `DokumnetacioKepek/backend_tests.png`

### Minta tesztkód
`server/tests/Feature/PingTest.php`
```php
$login = $this->login(self::ADMIN_EMAIL, self::PASSWORD);
$login->assertStatus(200);

$token = $this->myGetToken($login);
$response = $this->myGet("/api/services", $token);
$response->assertStatus(200);
```

## Frontend tesztek

### Unit teszt (Vitest)
Futtatás:
```console
cd client
npm run test:unit
```

Eredmény mentése:
```console
npm run test:unit > frontend-unit-test-results.txt
```

### E2E teszt (Cypress)
Futtatás:
```console
cd client
npm run test:e2e
```

Eredmény mentése:
```console
npm run test:e2e > frontend-e2e-test-results.txt
```

### Minta tesztkód
`client/src/__tests__/App.spec.js`
```js
import { describe, it, expect } from "vitest";
import { mount } from "@vue/test-utils";
import App from "../App.vue";

describe("App", () => {
  it("renders properly", () => {
    const wrapper = mount(App);
    expect(wrapper.exists()).toBe(true);
  });
});
```

## Teszteredmények dokumentálása
- A tesztek kimenete legyen fájlba mentve a repo gyökerében.
- A futásokról készült képernyőképek kerüljenek a `DokumnetacioKepek` mappába.
