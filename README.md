Запуск тестов

docker-compose build
docker-compose run --rm app composer install
docker-compose run --rm app vendor/bin/phpunit