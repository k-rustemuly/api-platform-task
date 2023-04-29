docker compose up

Данные админа: 
    admin@example.com
    123456


Если пользователь не найден:
docker compose exec php bin/console doctrine:fixtures:load
