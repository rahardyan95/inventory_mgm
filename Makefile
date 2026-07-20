.PHONY: up down build migrate seed fresh bash

up:
	@echo "Starting Docker containers..."
	docker-compose up -d

down:
	@echo "Stopping Docker containers..."
	docker-compose down

build:
	@echo "Building Docker images..."
	docker-compose build

migrate:
	@echo "Running migrations..."
	docker-compose exec app php artisan migrate

seed:
	@echo "Running seeders..."
	docker-compose exec app php artisan db:seed

fresh:
	@echo "Running fresh migrations and seeders..."
	docker-compose exec app php artisan migrate:fresh --seed

bash:
	@echo "Entering container shell..."
	docker-compose exec app sh
