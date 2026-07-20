# 🚀 Panduan Deploy VPS / Production

Dokumen ini menjelaskan cara men-deploy aplikasi **Enterprise Inventory Management System** ke VPS (Virtual Private Server) agar bisa diakses secara online.

---

## Spesifikasi VPS Minimum

| Komponen | Minimum | Rekomendasi |
|----------|---------|-------------|
| CPU      | 1 vCPU  | 2 vCPU      |
| RAM      | 1 GB    | 2 GB        |
| Storage  | 20 GB SSD | 40 GB SSD |
| OS       | Ubuntu 22.04 LTS | Ubuntu 22.04 LTS |

**Provider VPS yang direkomendasikan** (harga terjangkau untuk Indonesia):
- **DigitalOcean** — Droplet $6/bulan (1GB RAM) → digitalocean.com
- **Vultr** — $6/bulan → vultr.com
- **IDCloudHost** — Provider lokal Indonesia → idcloudhost.com
- **Niagahoster VPS** — Provider lokal → niagahoster.co.id

---

## Fase 1: Persiapan Domain & VPS

### 1.1 Beli Domain (Opsional tapi Direkomendasikan)
- Beli domain di Niagahoster, Namecheap, atau Cloudflare
- Contoh: `inventory.perusahaan.com`

### 1.2 Arahkan DNS ke VPS
Di panel DNS domain Anda, tambahkan:
```
Type: A Record
Name: @ (atau subdomain, misal: inventory)
Value: <IP_VPS_ANDA>
TTL: 3600
```

---

## Fase 2: Setup Server (Ubuntu 22.04)

Sambungkan ke VPS via SSH:
```bash
ssh root@<IP_VPS_ANDA>
```

### 2.1 Update Server & Install Docker

```bash
# Update paket
apt update && apt upgrade -y

# Install dependencies
apt install -y curl git ufw

# Install Docker Engine
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Install Docker Compose Plugin
apt install -y docker-compose-plugin

# Verifikasi instalasi
docker --version
docker compose version
```

### 2.2 Konfigurasi Firewall (UFW)

```bash
# Izinkan SSH, HTTP, HTTPS
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable

# Cek status
ufw status
```

### 2.3 Buat User Non-Root (Opsional tapi Direkomendasikan)

```bash
adduser deploy
usermod -aG sudo deploy
usermod -aG docker deploy

# Login sebagai user baru
su - deploy
```

---

## Fase 3: Deploy Aplikasi ke VPS

### 3.1 Clone/Upload Kode ke VPS

**Opsi A: Menggunakan Git (Direkomendasikan)**
```bash
cd /var/www
git clone <URL_REPO_GIT> inventory_mgm
cd inventory_mgm
```

**Opsi B: Upload via SCP dari komputer lokal**
```powershell
# Jalankan di komputer lokal (PowerShell/Terminal)
scp -r C:\Users\Rahardyan\Desktop\Project\inventory_mgm root@<IP_VPS>:/var/www/
```

### 3.2 Buat File .env Production

```bash
cd /var/www/inventory_mgm
cp backend/.env.example backend/.env

# Edit file .env untuk production
nano backend/.env
```

Isi dengan nilai production:
```env
APP_NAME="Inventory Management System"
APP_ENV=production          # WAJIB: ubah ke production
APP_DEBUG=false             # WAJIB: matikan debug di production
APP_URL=https://inventory.perusahaan.com  # URL domain Anda

# Database
DB_CONNECTION=pgsql
DB_HOST=db                  # Nama service di docker-compose
DB_PORT=5432
DB_DATABASE=inventory_mgm
DB_USERNAME=inventory_user
DB_PASSWORD=GANTI_DENGAN_PASSWORD_KUAT_123!  # WAJIB ganti!

# Session & Cache (gunakan redis untuk production)
SESSION_DRIVER=cookie
CACHE_STORE=array
```

### 3.3 Modifikasi docker-compose.yml untuk Production

Buat file `docker-compose.prod.yml` di root proyek:

```bash
nano /var/www/inventory_mgm/docker-compose.prod.yml
```

```yaml
# docker-compose.prod.yml — Konfigurasi Production
services:
  app:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: inventory_app
    restart: always         # Selalu restart otomatis
    working_dir: /var/www/html
    volumes:
      - ./backend:/var/www/html
      - ./backend/storage/logs:/var/www/html/storage/logs
    environment:
      - APP_ENV=production
    depends_on:
      - db
    networks:
      - inventory_network

  web:
    image: nginx:alpine
    container_name: inventory_web
    restart: always
    ports:
      - "80:80"             # HTTP (akan diupgrade ke HTTPS oleh Certbot)
      - "443:443"           # HTTPS
    volumes:
      - ./backend:/var/www/html
      - ./backend/nginx/default.conf:/etc/nginx/conf.d/default.conf
      - /etc/letsencrypt:/etc/letsencrypt:ro  # SSL Certificates
    depends_on:
      - app
    networks:
      - inventory_network

  db:
    image: postgres:16-alpine
    container_name: inventory_db
    restart: always
    environment:
      POSTGRES_USER: ${DB_USERNAME}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
      POSTGRES_DB: ${DB_DATABASE}
    volumes:
      - inventory_db_data:/var/lib/postgresql/data
    networks:
      - inventory_network
    # PENTING: Jangan expose port 5432 ke publik di production!

networks:
  inventory_network:
    driver: bridge

volumes:
  inventory_db_data:
```

### 3.4 Build dan Jalankan

```bash
cd /var/www/inventory_mgm

# Build image production
docker compose -f docker-compose.prod.yml build

# Jalankan semua kontainer
docker compose -f docker-compose.prod.yml up -d

# Setup Laravel (hanya pertama kali)
docker compose -f docker-compose.prod.yml exec app composer install --no-dev --optimize-autoloader
docker compose -f docker-compose.prod.yml exec app php artisan key:generate
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec app php artisan db:seed --force
docker compose -f docker-compose.prod.yml exec app php artisan storage:link

# Optimize Laravel untuk production
docker compose -f docker-compose.prod.yml exec app php artisan optimize
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache
```

---

## Fase 4: Setup HTTPS dengan SSL (Let's Encrypt)

### 4.1 Install Certbot

```bash
apt install -y certbot python3-certbot-nginx
```

### 4.2 Hentikan kontainer web sementara

```bash
docker compose -f docker-compose.prod.yml stop web
```

### 4.3 Dapatkan Sertifikat SSL

```bash
# Ganti dengan domain Anda yang sebenarnya
certbot certonly --standalone -d inventory.perusahaan.com

# Sertifikat akan tersimpan di:
# /etc/letsencrypt/live/inventory.perusahaan.com/
```

### 4.4 Update Konfigurasi Nginx untuk HTTPS

```bash
nano /var/www/inventory_mgm/backend/nginx/default.conf
```

```nginx
# Redirect HTTP ke HTTPS
server {
    listen 80;
    server_name inventory.perusahaan.com;
    return 301 https://$host$request_uri;
}

# Server HTTPS utama
server {
    listen 443 ssl;
    server_name inventory.perusahaan.com;
    root /var/www/html/public;

    # SSL Certificates dari Let's Encrypt
    ssl_certificate /etc/letsencrypt/live/inventory.perusahaan.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/inventory.perusahaan.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 4.5 Nyalakan kembali kontainer web

```bash
docker compose -f docker-compose.prod.yml start web
```

### 4.6 Auto-Renew SSL (Crontab)

```bash
crontab -e

# Tambahkan baris ini untuk auto-renew setiap hari jam 2 pagi:
0 2 * * * certbot renew --quiet && docker compose -f /var/www/inventory_mgm/docker-compose.prod.yml restart web
```

---

## Fase 5: Update Aplikasi (Deployment Rutin)

Setiap kali ada perubahan kode, gunakan alur ini:

```bash
cd /var/www/inventory_mgm

# 1. Pull kode terbaru dari Git
git pull origin main

# 2. Rebuild image jika ada perubahan Dockerfile
docker compose -f docker-compose.prod.yml build app

# 3. Jalankan ulang kontainer
docker compose -f docker-compose.prod.yml up -d

# 4. Jalankan migrasi (jika ada perubahan database)
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# 5. Bersihkan cache
docker compose -f docker-compose.prod.yml exec app php artisan optimize:clear
docker compose -f docker-compose.prod.yml exec app php artisan optimize
```

---

## Fase 6: Monitoring & Maintenance

### Cek Status Kontainer

```bash
docker compose -f docker-compose.prod.yml ps
```

### Lihat Log

```bash
# Log Nginx (request HTTP)
docker compose -f docker-compose.prod.yml logs web

# Log Laravel
docker compose -f docker-compose.prod.yml exec app tail -f storage/logs/laravel.log
```

### Backup Otomatis Database

```bash
# Tambahkan ke crontab untuk backup harian
crontab -e

# Backup setiap hari jam 1 pagi, simpan 7 hari terakhir
0 1 * * * docker compose -f /var/www/inventory_mgm/docker-compose.prod.yml exec -T db pg_dump -U inventory_user inventory_mgm > /var/backups/db_$(date +\%Y\%m\%d).sql && find /var/backups/ -name "db_*.sql" -mtime +7 -delete
```

---

## Checklist Deployment

- [ ] VPS dibeli dan SSH bisa diakses
- [ ] Docker & Docker Compose terinstall
- [ ] UFW Firewall dikonfigurasi (port 80, 443, 22)
- [ ] Domain diarahkan ke IP VPS
- [ ] File `.env` production dibuat dengan nilai yang benar
- [ ] `APP_DEBUG=false` di `.env`
- [ ] Password database sudah diganti dari default
- [ ] `docker compose up -d` berhasil
- [ ] `php artisan migrate --force` berhasil
- [ ] Website bisa diakses via `http://IP_VPS`
- [ ] SSL Let's Encrypt dikonfigurasi
- [ ] Website bisa diakses via `https://domain.com`
- [ ] Backup database otomatis di-setup
- [ ] Mobile App `api.js` diupdate ke URL production
