# Laravel + Vue Bookstore Demo

Небольшое SPA-приложение книжного каталога на **Laravel + Vue 3**. Проект сделан как практическая демонстрация работы со стеком: REST API, Eloquent, валидация, авторизация, JWT, Policies, feature-тесты и Vue SPA.

> Это демонстрационный проект, а не коммерческий продукт. Его задача — показать практическую работу с Laravel и Vue и основные решения, которые я использую при разработке backend/API.

## Что реализовано

- каталог книг с поиском, фильтрами, сортировкой и пагинацией;
- синхронизация фильтров каталога с query string;
- страница книги и блок похожих книг;
- корзина с хранением состояния в `localStorage`;
- JWT-аутентификация: login, logout, получение текущего пользователя;
- создание книг авторизованным пользователем;
- редактирование и удаление только собственных книг;
- серверная валидация через Laravel Form Requests;
- преобразование API-ответов через Laravel API Resources;
- разграничение доступа через Laravel Policies;
- factories и seeders для демонстрационных данных;
- feature-тесты API, аутентификации, прав доступа и валидации.

## Стек

### Backend

- PHP 8.3+
- Laravel 13
- Eloquent ORM
- SQLite для локального запуска
- REST API
- JWT Auth (`php-open-source-saver/jwt-auth`)
- PHPUnit / Laravel Feature Tests

### Frontend

- Vue 3
- Composition API
- Vue Router
- Axios
- Tailwind CSS
- Vite

## Структура backend

Основной поток обработки данных построен стандартными механизмами Laravel:

```text
Route
  -> Controller
    -> FormRequest
      -> Eloquent Model
        -> API Resource
```

Права на изменение и удаление книг проверяются через `BookPolicy`.

## Быстрый запуск

Требования:

- PHP 8.3+
- Composer
- Node.js + npm

```bash
git clone https://github.com/SoslanGit/spa.git
cd spa
composer setup
```

Команда `composer setup`:

1. устанавливает PHP-зависимости;
2. создаёт `.env` из `.env.example`;
3. генерирует `APP_KEY` и `JWT_SECRET`;
4. создаёт локальную SQLite-базу;
5. запускает миграции и seeders;
6. устанавливает frontend-зависимости;
7. собирает frontend.

Для разработки можно запустить Laravel и Vite отдельно:

```bash
php artisan serve
npm run dev
```

Приложение Laravel по умолчанию будет доступно на `http://127.0.0.1:8000`.

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
| `GET` | `/api/books` | Нет | Получить каталог |
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

## Осознанные упрощения демо-проекта

Каталог небольшой, поэтому поиск, фильтрация, сортировка и пагинация сейчас выполняются на клиенте. Для большого production-каталога эти операции логичнее перенести на backend и использовать серверную пагинацию Laravel.

JWT хранится в `localStorage`, чтобы в рамках проекта продемонстрировать token-based API authentication. Для same-origin production SPA я также рассматривал бы Laravel Sanctum с cookie-based аутентификацией и `HttpOnly` cookies в зависимости от требований проекта.

## Автор

Проект разработан как практическая демонстрация Laravel + Vue при переходе от коммерческой PHP/backend-разработки к Laravel-стеку.
