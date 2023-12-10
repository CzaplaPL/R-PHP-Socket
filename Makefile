build:
	docker compose build
composer:
	#docker compose run main composer install
	#docker compose run workers composer install
	#docker compose run currency composer install
	#docker compose run bonuses composer install
	docker compose run provision composer install
run:
	docker compose up
createNetwork:
	docker network create --gateway 172.26.0.1 --subnet 172.26.0.0/24 app-nginx-proxy
