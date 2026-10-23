# Тестовое задание

## Запуск

Требуются Docker и Docker Compose.

1. Создать `.env` из `.env.example` и указать параметры подключения к PostgreSQL
2. Собрать и запустить контейнеры:

   ```bash
   docker compose up -d --build
   ```

3. Установить зависимости

   ```bash
   docker compose exec app composer install
   ```

4. Сгенерировать ключ приложения

   ```bash
   docker compose exec app php artisan key:generate
   ```

5. Выполнить миграции

   ```bash
   docker compose exec app php artisan migrate
   ```

API доступен по адресу `http://localhost:8080`.

## Документация

- Swagger: [http://localhost:8080/docs/index.html](http://localhost:8080/docs/index.html)
- Спецификация OpenAPI: `public/docs/openapi.yaml`

## Тесты

Для тестов создана отдельная БД `task_manager_test`.

```bash
docker compose exec app php artisan test
```

Проверка форматирования

```bash
docker compose exec app ./vendor/bin/pint --test
```
