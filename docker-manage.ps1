param (
    [Parameter(Position = 0)]
    [string]$Action
)

switch ($Action) {
    "up" {
        Write-Host "Memulai container Docker..." -ForegroundColor Green
        docker-compose up -d
    }
    "down" {
        Write-Host "Menghentikan container Docker..." -ForegroundColor Yellow
        docker-compose down
    }
    "build" {
        Write-Host "Build ulang image Docker..." -ForegroundColor Cyan
        docker-compose build
    }
    "migrate" {
        Write-Host "Menjalankan migrasi database di container..." -ForegroundColor Green
        docker-compose exec app php artisan migrate
    }
    "seed" {
        Write-Host "Menjalankan database seeder di container..." -ForegroundColor Green
        docker-compose exec app php artisan db:seed
    }
    "fresh" {
        Write-Host "Migrasi ulang (fresh) dan seed di container..." -ForegroundColor Green
        docker-compose exec app php artisan migrate:fresh --seed
    }
    "bash" {
        docker-compose exec app sh
    }
    default {
        Write-Host "Penggunaan: .\docker-manage.ps1 [aksi]"
        Write-Host "Aksi yang tersedia:"
        Write-Host "  up      : Mulai container di background"
        Write-Host "  down    : Hentikan dan hapus container"
        Write-Host "  build   : Build ulang image"
        Write-Host "  migrate : Jalankan php artisan migrate"
        Write-Host "  seed    : Jalankan php artisan db:seed"
        Write-Host "  fresh   : Jalankan php artisan migrate:fresh --seed"
        Write-Host "  bash    : Masuk ke dalam shell container app"
    }
}
