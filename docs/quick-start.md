# Быстрый старт

## Запуск через Docker

1. Клонируйте проект:

   ```bash
   git clone <repository-url>
   cd <project-directory>
   ```

2. Создайте файлы окружения:

   ```bash
   cp .env.example .env
   cp .env.mysql.example .env.mysql
   ```

3. Проверьте настройки БД. Значения в файлах должны соответствовать друг другу:

   | `.env` | `.env.mysql` |
   |---|---|
   | `DB_NAME` | `MYSQL_DATABASE` |
   | `DB_USER` | `MYSQL_USER` |
   | `DB_PASSWORD` | `MYSQL_PASSWORD` |

   Для запуска через Docker используйте:

   ```dotenv
   DB_HOST=taskforce-yii2-mysql
   DB_PORT=3306
   ```

4. Запустите проект:

   ```bash
   ./start.sh
   ```

5. Откройте <http://localhost:8080>.

Остановка проекта:

```bash
./stop.sh
```

## Внешние API

Для карт, геокодирования и входа через GitHub заполните в `.env`:

```dotenv
YANDEX_GEOCODER_API_KEY=
YANDEX_MAPS_API_KEY=
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
```

Без ключей проект запустится, но связанная с ними функциональность будет недоступна.

## База данных

При первом запуске `data/sql/schema.sql` выполняется автоматически. Данные MySQL сохраняются в `docker/mysql/data`, поэтому при последующих запусках схема повторно не применяется.

Чтобы пересоздать базу после изменения схемы:

```bash
docker rm -f taskforce-yii2-mysql
rm -rf docker/mysql/data/*
./start.sh
```

> Внимание: эти команды полностью удалят локальные данные MySQL.

## Запуск PHP без Docker

MySQL можно оставить в Docker, а PHP запустить на хосте. Укажите в `.env`:

```dotenv
DB_HOST=localhost
DB_PORT=3309
```

Затем выполните:

```bash
docker start taskforce-yii2-mysql
composer install
php yii serve
```

Приложение будет доступно по адресу <http://localhost:8080>. Перед возвратом к Docker-запуску верните `DB_HOST=taskforce-yii2-mysql` и `DB_PORT=3306`.

## Полезные команды

```bash
# Контейнеры и логи
docker ps -a
docker logs taskforce-yii2-mysql
docker logs taskforce-yii2-php

# Управление MySQL-контейнером
docker start taskforce-yii2-mysql
docker stop taskforce-yii2-mysql
docker restart taskforce-yii2-mysql

# Диагностика сети
docker network inspect taskforce-yii2
docker exec taskforce-yii2-php getent hosts taskforce-yii2-mysql

# PHP и Composer в контейнере
docker exec taskforce-yii2-php php -v
docker exec taskforce-yii2-php composer install
```
