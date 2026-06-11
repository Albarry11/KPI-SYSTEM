# KPI System — Sistem Penilaian Kinerja Karyawan
## Proyek Akhir Praktikum Teknologi Cloud Computing

---

## Struktur Project

```
kpi-system-v2/
├── schema.sql                 ← Struktur tabel Cloud SQL (MySQL)
├── firestore-schema.js        ← Dokumentasi koleksi Firestore
│
├── backend/                   ← REST API (Laravel 10 + Sanctum)
│   ├── Dockerfile
│   ├── docker/                ← nginx, supervisord, php.ini
│   ├── app/
│   │   ├── Http/Controllers/  ← 9 Controller
│   │   ├── Http/Middleware/   ← RoleMiddleware
│   │   └── Models/            ← 8 Model
│   ├── database/
│   │   ├── migrations/        ← 4 Migration files
│   │   └── seeders/           ← Sample data seeder
│   ├── routes/api.php         ← Definisi endpoint REST API
│   └── config/                ← Laravel configs
│
├── frontend/                  ← Web Manager (React + Vite)
│   ├── Dockerfile
│   └── src/
│       ├── pages/             ← Login, Dashboard, KPI Weight, dll
│       ├── components/        ← Sidebar
│       └── utils/api.js       ← Axios instance
│
└── mobile/                    ← Mobile PWA (React + Vite)
    ├── Dockerfile
    └── src/
        └── pages/             ← Login, Home, Update Progress, dll
```

---

## Arsitektur Sistem

```
[Manajer — Browser]                    [Karyawan — HP]
      |                                       |
   Web Manager                          Mobile PWA
  (React/Vite)                         (React/Vite)
      |                                       |
      └──────────────┬────────────────────────┘
                     ↓
            REST API — Laravel 10
           (Google Cloud Run)
                     |
          ┌──────────┴──────────┐
          ↓                     ↓
    Cloud SQL (MySQL)      Cloud Firestore
    via phpMyAdmin          (NoSQL - realtime)
    - users                - activityLogs
    - periods              - evaluationComments
    - kpi_categories       - workEvidence
    - kpi_weights          - notifications
    - tasks                - chatThreads
    - task_progress
    - evaluations
    - reports
```

---

## Setup & Deploy di Google Cloud Platform

### 0. Prerequisites
- Google Cloud SDK (gcloud) terinstall
- Node.js 18+ & npm
- PHP 8.2+ & Composer (untuk dev lokal)
- Docker (untuk build container)
- Akun GCP dengan billing aktif

### 1. Buat Project GCP
```bash
gcloud projects create YOUR-PROJECT-ID
gcloud config set project YOUR-PROJECT-ID
gcloud auth configure-docker
```

### 2. Aktifkan Services
```bash
gcloud services enable \
  sqladmin.googleapis.com \
  run.googleapis.com \
  firestore.googleapis.com \
  storage.googleapis.com \
  cloudbuild.googleapis.com \
  artifactregistry.googleapis.com
```

### 3. Buat Cloud SQL (MySQL 8.0)
```bash
# Buat instance
gcloud sql instances create kpi-db \
  --database-version=MYSQL_8_0 \
  --tier=db-f1-micro \
  --region=asia-southeast2 \
  --root-password=YOUR_ROOT_PASSWORD

# Buat database
gcloud sql databases create kpi_system --instance=kpi-db

# Buat user
gcloud sql users create kpi_user \
  --instance=kpi-db \
  --password=YOUR_USER_PASSWORD \
  --host=%

# Catat Public IP
gcloud sql instances describe kpi-db --format="value(ipAddresses[0].ipAddress)"
```

### 4. Import Schema ke Cloud SQL
```bash
# Opsi 1: Via Cloud Shell
gcloud sql connect kpi-db --user=root
# Lalu copy-paste isi schema.sql

# Opsi 2: Via phpMyAdmin (deploy di Cloud Run)
# Lihat langkah 4b di bawah
```

### 4b. Setup phpMyAdmin (Opsional)
```bash
gcloud run deploy phpmyadmin \
  --image=phpmyadmin/phpmyadmin \
  --region=asia-southeast2 \
  --allow-unauthenticated \
  --set-env-vars="PMA_HOST=YOUR_CLOUD_SQL_IP,PMA_PORT=3306" \
  --port=80
```

### 5. Setup Firestore
```bash
gcloud firestore databases create --region=asia-southeast2
```

### 6. Buat Cloud Storage Bucket
```bash
gsutil mb -l asia-southeast2 gs://YOUR-PROJECT-ID-evidence
```

### 7. Deploy Backend ke Cloud Run
```bash
cd backend

# Set environment variables
gcloud run deploy kpi-backend \
  --source . \
  --region=asia-southeast2 \
  --allow-unauthenticated \
  --set-env-vars="\
APP_NAME=KPI System,\
APP_ENV=production,\
APP_KEY=base64:$(openssl rand -base64 32),\
APP_DEBUG=false,\
DB_CONNECTION=mysql,\
DB_HOST=YOUR_CLOUD_SQL_IP,\
DB_PORT=3306,\
DB_DATABASE=kpi_system,\
DB_USERNAME=kpi_user,\
DB_PASSWORD=YOUR_PASSWORD,\
GCP_PROJECT_ID=YOUR-PROJECT-ID,\
LOG_CHANNEL=stderr"
```

### 8. Deploy Web Manager ke Cloud Run
```bash
cd frontend

# Update .env dengan URL backend
echo "VITE_API_URL=https://kpi-backend-XXXXX.asia-southeast2.run.app/api" > .env
npm run build

gcloud run deploy kpi-frontend \
  --source . \
  --region=asia-southeast2 \
  --allow-unauthenticated
```

### 9. Deploy Mobile PWA ke Cloud Run
```bash
cd mobile

# Update .env dengan URL backend
echo "VITE_API_URL=https://kpi-backend-XXXXX.asia-southeast2.run.app/api" > .env
npm run build

gcloud run deploy kpi-mobile \
  --source . \
  --region=asia-southeast2 \
  --allow-unauthenticated
```

---

## Development Lokal

### Backend
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# Edit .env → set DB_HOST ke Cloud SQL IP
php artisan migrate
php artisan db:seed
php artisan serve --port=8080
```

### Frontend (Web Manager)
```bash
cd frontend
npm install
# .env sudah ada (VITE_API_URL=http://localhost:8080/api)
npm run dev
# Buka http://localhost:5173
```

### Mobile (PWA)
```bash
cd mobile
npm install
# .env sudah ada (VITE_API_URL=http://localhost:8080/api)
npm run dev
# Buka http://localhost:5174
```

---

## Akun Testing

| Username | Password | Role |
|----------|----------|------|
| manager | password123 | Manager |
| andi | password123 | Employee |
| sari | password123 | Employee |
| budi | password123 | Employee |
| dewi | password123 | Employee |

---

## Fitur Utama

### Platform 1 — Web Manajer
| Fitur | Keterangan |
|-------|------------|
| Dashboard | Ringkasan metrik: total karyawan, rata-rata KPI, yang di bawah target |
| Input Bobot KPI | Form untuk set bobot & target per karyawan |
| Tabel Evaluasi | Progress bar + skor + grade tiap karyawan |
| Generate Evaluasi | Hitung skor otomatis berdasarkan bobot × progres |
| Approve & Notif | Approve hasil → notifikasi otomatis ke karyawan |
| Filter & Cari | Filter by departemen, search by nama |

### Platform 2 — Mobile Karyawan (PWA)
| Fitur | Keterangan |
|-------|------------|
| Ring Score | Visualisasi skor KPI bulan ini |
| Task List | Daftar target aktif + progress bar per task |
| Update Progres | Input nilai progres terbaru |
| Upload Bukti | Upload foto/PDF bukti kerja ke Cloud Storage |
| Notifikasi | Terima notifikasi dari manajer real-time |
| PWA | Bisa di-install di HP seperti app native |

---

## Rumus Perhitungan Skor KPI

```
Skor KPI = Σ (Progres% × Bobot_i) / Total_Bobot

Contoh:
- Penyelesaian Tiket: 90% tercapai × bobot 40% = 36
- Code Review:        60% tercapai × bobot 35% = 21
- Dokumentasi:        30% tercapai × bobot 25% = 7.5
                                          Total = 64.5 / 100
```

| Grade | Rentang Skor |
|-------|-------------|
| A     | 90 – 100    |
| B     | 75 – 89     |
| C     | 60 – 74     |
| D     | 50 – 59     |
| E     | < 50        |

---

## API Endpoints

### Public
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| POST | /api/auth/login | Login (username + password) |
| POST | /api/auth/register | Register karyawan baru |
| POST | /api/setup-manager | Setup manager pertama |

### Protected (perlu token)
| Method | Endpoint | Role | Keterangan |
|--------|----------|------|------------|
| GET | /api/auth/me | All | Data user login |
| POST | /api/auth/logout | All | Logout |
| GET | /api/dashboard | Manager | Data dashboard |
| GET/POST/PUT/DELETE | /api/kpi-weights | Manager | CRUD bobot KPI |
| GET/POST | /api/evaluations | Manager | Evaluasi |
| POST | /api/evaluations/generate | Manager | Generate skor |
| POST | /api/evaluations/{id}/approve | Manager | Approve evaluasi |
| GET/POST/PUT/DELETE | /api/periods | Manager | CRUD periode |
| GET/POST/DELETE | /api/reports | Manager | Laporan |
| GET | /api/tasks | Employee | Tugas saya |
| POST | /api/task-progress | Employee | Update progres |
| GET | /api/evaluations/my | Employee | Evaluasi saya |
| GET/PATCH | /api/notifications | Employee | Notifikasi |
| GET/POST | /api/chats | All | Chat threads |

---

## Teknologi yang Digunakan

| Layer | Teknologi |
|-------|-----------| 
| Cloud Provider | Google Cloud Platform |
| SQL Database | Cloud SQL (MySQL 8.0) |
| NoSQL Database | Cloud Firestore |
| File Storage | Cloud Storage (GCS) |
| Backend API | PHP 8.2 + Laravel 10 (Cloud Run) |
| Auth | Laravel Sanctum (Token-based) |
| Web Manager | React 18 + Vite 5 |
| Mobile App | React 18 + Vite 5 + PWA |
| Containerization | Docker |
| Deployment | Google Cloud Run |
| DB Management | phpMyAdmin |

---

*Proyek Akhir Praktikum Teknologi Cloud Computing — 2026*
