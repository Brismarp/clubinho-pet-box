.PHONY: install up down logs shell db-import serve composer-dump backup

install:
	docker compose run --rm web composer install

up:
	docker compose up -d --build
	docker compose exec web composer dump-autoload -o

down:
	docker compose down

logs:
	docker compose logs -f

shell:
	docker compose exec web bash

db-import:
	docker compose exec -T db mysql -uclubinho -pclubinho clubinhopet < database/docker/01_schema.sql

serve:
	php -S localhost:8080 -t public

composer-dump:
	docker compose exec web composer dump-autoload -o

backup:
	bash scripts/backup.sh
