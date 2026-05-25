# UnD – Unemployment Data Visualizer

UnD este o aplicație Web pentru vizualizarea datelor publice despre șomajul din România. Permite filtrarea și analiza datelor după județ și perioadă de timp, cu vizualizări grafice multi-criteriale (rata șomajului, mediu urban/rural, grupe de vârstă, nivel de educație) și o hartă interactivă a României.

## Tehnologii utilizate

- **Frontend:** HTML, CSS, JavaScript (Vanilla)
- **Backend:** PHP
- **Bază de date:** SQLite (via PDO)
- **Grafice:** Chart.js
- **Hartă interactivă:** Leaflet.js + OpenStreetMap
- **Server local:** XAMPP (Apache + PHP)
- **Export:** CSV, JSON, SVG, PDF

## Structura proiectului

```
/Lidl_Restanta/
  admin/          -> modul de administrare (HTML + PHP)
  api/            -> endpoint-uri PHP pentru API și import/export
  config/         -> configurare conexiune bază de date
  data/           -> fișiere CSV cu datele despre șomaj
  public/         -> aplicația principală (HTML, CSS, JS)
  README.md
  LICENSE
```

## Cum rulez local

1. Instalează XAMPP
2. Clonează sau descarcă proiectul în folderul `htdocs`
3. Pornește Apache din XAMPP Control Panel
4. Accesează în browser: `http://localhost/Lidl_Restanta/admin/login.html`
5. Autentifică-te cu credențialele de admin (vezi secțiunea Admin)
6. Apasă **Initializeaza baza de date** – creează tabelele și importă toate fișierele CSV
7. Accesează aplicația principală: `http://localhost/Lidl_Restanta/public/index.html`

## Modul de administrare

Accesibil la: `http://localhost/Lidl_Restanta/admin/login.html`

Utilizator: `admin` 
Parolă: `admin123`

Funcționalități disponibile în panoul de admin:
- Inițializare bază de date (prima rulare sau reset complet)
- Vizualizarea statisticilor per tabel (număr rânduri, interval de luni acoperit)
- Reimport CSV (actualizare date fără ștergerea tabelelor)

## Date disponibile

Rata șomajului: ian 2024 – mai 2025
Urban / Rural: dec 2024 – mai 2025
Grupe de vârstă: dec 2024 – mai 2025
Nivel de educație: dec 2024 – mai 2025

Sursa datelor: https://data.gov.ro

## Securitate

- Interogările SQL folosesc prepared statements (protecție SQL Injection)
- Datele afișate în HTML sunt escapate cu `htmlspecialchars` (protecție XSS)
- Modulul de administrare este protejat prin autentificare cu sesiuni PHP

## Licență

Acest proiect este publicat sub licență [MIT](./LICENSE)
