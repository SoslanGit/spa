# Laravel + Vue Bookstore Demo

## Что реализовано

### Backend и архитектура

- REST API с разделением ответственности между слоями Laravel;
- работа с данными через **Eloquent ORM**;
- серверная валидация через **Form Requests**;
- формирование API-ответов через **API Resources**;
- авторизация действий пользователей через **Laravel Policies**;
- JWT-аутентификация: login, logout и получение текущего пользователя;

- кэширование каталога книг в **Redis** по схеме **cache-aside**;
- автоматическая инвалидация Redis-кэша через `BookObserver` при создании, изменении и удалении книг;
- работа с кэшем вынесена в отдельный сервис `BookCatalogCache`;
- в Redis сохраняются обычные массивы данных без сериализации Eloquent-моделей;

- асинхронная обработка событий книг через **RabbitMQ**;
- отдельная очередь `books` и отдельный `queue-worker`;
- фоновые задачи реализованы через Job `LogBookEvent`;
- для Job настроены повторные попытки, `timeout` и `backoff`;

- приложение, PostgreSQL, Redis, RabbitMQ и queue worker работают в отдельных **Docker-контейнерах**;
- используются factories и seeders для демонстрационных данных;
- реализованы feature-тесты API, аутентификации, валидации и прав доступа.

### Функциональность приложения

- каталог книг с поиском, фильтрами, сортировкой и пагинацией;
- синхронизация фильтров каталога с query string;
- отдельная страница книги и блок похожих книг;
- корзина с сохранением состояния в `localStorage`;
- создание книг авторизованным пользователем;
- редактирование и удаление только собственных книг;
- запрет изменения и удаления чужих книг через `BookPolicy`.

### Что происходит при изменении книги

Создание, редактирование или удаление книги запускает независимые реакции на уровне модели:

```text
Book
 ↓
BookObserver
 ├── Redis → инвалидация кэша каталога
 │
 └── RabbitMQ → очередь books
                 ↓
             queue-worker
                 ↓
            LogBookEvent
```

Redis используется для ускорения повторного чтения каталога и поддержания актуальности кэша. RabbitMQ передаёт фоновые задачи отдельному worker-процессу, не смешивая их выполнение с HTTP-запросом.

## Стек

### Backend

- PHP 8.3+
- Laravel 13
- Eloquent ORM
- PostgreSQL
- REST API
- JWT Auth (`php-open-source-saver/jwt-auth`)
- Redis / PhpRedis
- RabbitMQ
- `vladimir-yuldashev/laravel-queue-rabbitmq`
- PHPUnit / Laravel Feature Tests

### Frontend

- Vue 3
- Composition API
- Vue Router
- Axios
- Tailwind CSS
- Vite

### Инфраструктура

- Docker
- PostgreSQL container
- Redis container
- RabbitMQ container
- отдельный `queue-worker` для фоновой обработки задач
- RabbitMQ Management UI для контроля очередей

## Архитектура backend

Базовый HTTP-поток построен стандартными механизмами Laravel:

```text
Route
  -> Controller
    -> FormRequest
      -> Eloquent Model
        -> API Resource
```

Права на изменение и удаление книг проверяются через `BookPolicy`.

Контроллеры не содержат деталей работы с Redis и RabbitMQ. Кэширование вынесено в отдельный сервис, а реакция на изменения модели — в Observer и Job.

### Чтение каталога

Для списка книг используется cache-aside подход:

```text
GET /api/books
      ↓
BookController
      ↓
BookCatalogCache
      ↓
проверка Redis
   /        \
 hit        miss
  ↓           ↓
Redis     PostgreSQL
  ↓           ↓
  │       преобразование
  │       моделей в массивы
  │           ↓
  └────── Redis cache
              ↓
       данные каталога
```

Если каталог уже находится в Redis, запрос к PostgreSQL для этих данных не выполняется. При cache miss данные загружаются из БД, преобразуются в обычные массивы и сохраняются в Redis.

При необходимости после чтения из кэша модели восстанавливаются в памяти через `Book::hydrate()`.

### Изменение книги

Изменение модели запускает две независимые реакции:

```text
                    Book
                     ↓
                BookObserver
                 /        \
                /          \
               ↓            ↓
       Redis cache        RabbitMQ
       invalidation          ↓
                         queue books
                              ↓
                         queue-worker
                              ↓
                        LogBookEvent
```

При создании, редактировании или удалении книги Observer инвалидирует кэш каталога. Следующий запрос снова получает актуальные данные из PostgreSQL и формирует новый Redis-кэш.

При создании и редактировании книги Observer также отправляет `LogBookEvent` в RabbitMQ. HTTP-запрос не выполняет эту фоновую работу самостоятельно: Job забирает отдельный worker.

## Redis

Redis используется для кэширования списка книг.

Логика кэша вынесена из контроллера в сервис:

```text
BookCatalogCache
```

Он отвечает за:

- получение каталога из кэша;
- создание кэша;
- удаление кэша.

### Почему в Redis хранятся массивы, а не Eloquent Collection

На первой итерации в Redis сохранялась целиком `Eloquent Collection`. В Laravel 13 по умолчанию отключена десериализация PHP-классов из кэша:

```php
'serializable_classes' => false
```

При чтении сериализованной Eloquent-коллекции это приводило к `incomplete object`.

Вместо отключения этой защиты архитектура была изменена: Redis хранит только обычные массивы данных. Это позволяет не сериализовать Laravel-модели и не ослаблять безопасные настройки фреймворка.

Текущая схема:

```text
cache hit  -> массивы из Redis
cache miss -> PostgreSQL -> Eloquent -> массивы -> Redis
```

Если дальше нужны объекты `Book`, они восстанавливаются в памяти:

```php
Book::hydrate($cachedBooks)
```

### Инвалидация кэша

Для автоматической инвалидации используется `BookObserver`.

При событиях модели:

```text
created
updated
deleted
```

Observer очищает кэш каталога. Поэтому приложение не продолжает отдавать устаревший список после изменения данных.

Практически проверялся полный цикл:

```text
GET /api/books
→ ключ появляется в Redis

изменение Book через Eloquent
→ Observer удаляет ключ

следующий GET /api/books
→ данные снова читаются из PostgreSQL
→ Redis-кэш создаётся заново
```

## RabbitMQ

RabbitMQ используется для фоновой асинхронной обработки событий.

Laravel подключён к RabbitMQ через:

```text
vladimir-yuldashev/laravel-queue-rabbitmq
```

Для событий книг используется отдельная очередь:

```text
books
```

Фоновую обработку выполняет отдельный worker:

```bash
php artisan queue:work rabbitmq --queue=books
```

В Docker он работает отдельно от HTTP-приложения Laravel.

### Поток обработки Job

```text
Book created / updated
        ↓
BookObserver
        ↓
LogBookEvent::dispatch()
        ↓
RabbitMQ
        ↓
queue "books"
        ↓
queue-worker
        ↓
LogBookEvent::handle()
```

Сейчас `LogBookEvent` выполняет демонстрационное асинхронное логирование события книги. При обновлении в Job также передаются изменённые поля.

Пример результата обработки:

```text
Событие книги обработано через RabbitMQ
{"book_id":17,"event":"created","title":"Очень чистый код","changes":[]}
```

Для Job настроены повторные попытки и ограничение времени выполнения:

```text
tries = 3
timeout = 30 секунд
```

Worker запускается с `backoff`, поэтому временная ошибка не требует повторной отправки HTTP-запроса пользователем — задача может быть обработана повторно очередью.

RabbitMQ Management UI используется для наблюдения за очередью. Например, при остановленном worker задача остаётся в `books` как `messages_ready`, а после запуска worker забирается и выполняется.

## Redis и RabbitMQ решают разные задачи

```text
Redis
→ ускоряет повторное чтение данных

RabbitMQ
→ передаёт фоновые задачи отдельному worker

Observer
→ реагирует на события Eloquent-модели

Job
→ описывает фоновую работу

Queue
→ хранит задачу до обработки

Worker
→ забирает Job из очереди и выполняет его
```

В результате HTTP-приложение не смешивает чтение каталога, управление кэшем и фоновую обработку событий в одном контроллере.

## Быстрый запуск

Требования:

- PHP 8.3+
- Composer
- Node.js + npm
- PostgreSQL
- Redis
- RabbitMQ

```bash
git clone https://github.com/SoslanGit/spa.git
cd spa
composer setup
```

Команда `composer setup` подготавливает Laravel-приложение и frontend:

1. устанавливает PHP-зависимости;
2. создаёт `.env` из `.env.example`;
3. генерирует `APP_KEY` и `JWT_SECRET`;
4. запускает миграции и seeders;
5. устанавливает frontend-зависимости;
6. собирает frontend.

Redis, RabbitMQ и queue worker запускаются как отдельные сервисы Docker-конфигурации проекта.

Для разработки Laravel и Vite можно запускать отдельно в соответствии с используемой Docker/local-конфигурацией.

## Демо-пользователь

После `php artisan migrate --seed` создаётся пользователь:

```text
Email: demo@bookstore.test
Password: password
```

От его имени можно создавать, редактировать и удалять собственные книги.

## API

| Method | Endpoint | Auth | Назначение |
|---|---|---|---|
| `POST` | `/api/login` | Нет | Получить JWT |
| `GET` | `/api/books` | Нет | Получить каталог; чтение использует Redis cache-aside |
| `GET` | `/api/me` | JWT | Текущий пользователь |
| `POST` | `/api/logout` | JWT | Завершить сессию / инвалидировать токен |
| `POST` | `/api/books` | JWT | Создать книгу |
| `PATCH` | `/api/books/{book}` | JWT + owner | Изменить свою книгу |
| `DELETE` | `/api/books/{book}` | JWT + owner | Удалить свою книгу |

Пример создания книги:

```json
{
  "title": "Designing Data-Intensive Applications",
  "author": "Martin Kleppmann",
  "genre": "engineering",
  "year": 2017,
  "pages": 616,
  "price": 2490,
  "description": "Demo book"
}
```

## Тесты

```bash
composer test
```

Feature-тестами проверяются, в частности:

- успешная и неуспешная JWT-аутентификация;
- доступ к `/api/me`;
- logout и инвалидирование токена;
- запрет создания книги для гостя;
- создание книги авторизованным пользователем;
- валидация входных данных;
- редактирование собственной книги;
- запрет редактирования и удаления чужой книги;
- удаление собственной книги.

Redis и RabbitMQ дополнительно проверялись на уровне фактического прохождения сценариев: появление и инвалидация cache key, постановка Job в очередь и обработка отдельным worker.

## UPD

Каталог небольшой, поэтому поиск, фильтрация, сортировка и пагинация сейчас выполняются на клиенте. Для большого production-каталога эти операции логичнее перенести на backend и использовать серверную пагинацию Laravel.

JWT хранится в `localStorage`, чтобы в рамках проекта продемонстрировать token-based API authentication. Для same-origin production SPA можно использовать Laravel Sanctum с cookie-based аутентификацией и `HttpOnly` cookies в зависимости от требований проекта.

`LogBookEvent` сейчас выполняет демонстрационное логирование. В production-проекте тот же механизм очередей можно использовать для отправки уведомлений, интеграций с внешними API, генерации файлов, индексации или другой длительной работы, которую не следует выполнять внутри HTTP-request.

