# Barber Shop – Időpontfoglaló Webalkalmazás

Ez a projekt egy **fodrászat számára készült webalkalmazás**, amely lehetővé teszi a vendégek számára az **online időpontfoglalást**, valamint külön felületet biztosít a **fodrászok** és az **adminisztrátor** számára az időpontok és felhasználók kezelésére.

A rendszer **Laravel (backend API)** és **Vue.js (frontend)** technológiákra épül.

---

## Fő funkciók

### Vendég (User) funkciók
- Regisztráció és bejelentkezés
- Elérhető fodrászok megtekintése
- Naptár alapú időpontválasztás
- **30 perces idősávok** közül időpont foglalása
- Több szolgáltatás kiválasztása egy időponthoz
- Saját időpontok megtekintése
- Időpont lemondása
- Értékelés leadása hajvágás után (**1–5 csillag + szöveges vélemény**)

---

### Fodrász (Barber) funkciók
- Saját **admin felület** bejelentkezés után
- Saját időpontok megtekintése
- Lefoglalt időpontok lemondása
- Szabadnapok megadása (**teljes nap kihagyása**)
- Profiladatok kezelése (bemutatkozás, profilkép)
- Referencia képek feltöltése

---

### Admin funkciók
- Új fodrászok létrehozása
- Fodrászok **aktiválása / deaktiválása**
- Felhasználók kezelése
- Teljes rendszer áttekintése

---

## Időpontfoglalás működése

- A rendszer **fix nyitvatartással** dolgozik
- Egy nap **30 perces idősávokra** van bontva
- Egy idősáv egyszerre csak **egy foglalást** tartalmazhat
- A foglalás során a rendszer figyelembe veszi:
  - a már lefoglalt időpontokat
  - a fodrász által megadott szabadnapokat

---

## Email értesítések

A rendszer automatikus **email értesítéseket** küld:
- sikeres időpontfoglalás után
- a hajvágás befejezése után értékelés kérés céljából

---

## Értékelési rendszer

- Értékelést csak **befejezett időponthoz** lehet leadni
- Egy időponthoz csak **egy értékelés** tartozhat
- Az értékelések a **fodrász profilján** jelennek meg

---

## Technológiai stack

- **Backend:** Laravel (REST API)
- **Frontend:** Vue.js
- **Adatbázis:** MySQL
- **Hitelesítés:** Laravel Auth / Sanctum
- **Időzített feladatok:** Laravel Scheduler
- **Email küldés:** Laravel Mail

---

## Projekt célja

A projekt célja egy **átlátható, könnyen használható online időpontfoglaló rendszer** készítése egy fodrászat számára, amely csökkenti a telefonos egyeztetés szükségességét, és megkönnyíti a vendégek és a fodrászok mindennapi munkáját.