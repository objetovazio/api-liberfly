# Todo List API with Authentication

This project provides an API for managing Todo Lists and their associated Tasks. The API includes user authentication using JWT (JSON Web Tokens) and features for registering users, logging in, and managing todos and tasks.

## Project Purpose

This project was developed as part of my application for the Pleno PHP Laravel Developer position at LiberFly. It showcases my skills in building a robust API using PHP and Laravel, emphasizing best practices in software development, including test-driven development, clean code architecture, and effective use of documentation. The aim is to demonstrate my technical capabilities and readiness to contribute to the team at LiberFly.

## Features

- **User Authentication**: Register, login, logout, and refresh tokens.
- **Todo List Management**: Create, view, update, and delete todo lists.
- **Task Management**: Add, view, update, and delete tasks for specific todo lists.


# How to Run the API LiberFly Project

## 1. Clone the Repository

First, clone the repository using the `git` command:

```bash
git clone https://github.com/objetovazio/api-liberfly.git
```

## 2. Navigate to the Project Directory

Change to the project directory you just cloned:

```bash
cd api-liberfly
```

## 3. Copy the `.env.example` File

Copy the `.env.example` file to create a new `.env` file:

```bash
cp .env.example .env
```

## 4. Check the Database Variables

Open the `.env` file in a text editor and verify the following database variables:

```plaintext
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=api_liberfly
DB_USERNAME=api_liberfly
DB_PASSWORD=api_liberfly
```

Make sure the values match your database configurations.

## 5. Install Dependencies

Run the following command to install the project's dependencies using Composer:

```bash
composer install
```

## 6. Start Docker Compose

To start the database and other services, use the following command:

```bash
docker-compose up --build -d
```

## 7. Run Migrations

Once the services are running, execute the migrations to create the tables in the database:

```bash
php artisan migrate
```

## 8. Start the Server

Finally, start the local server for the application:

```bash
php artisan serve
```

---

Now you should have the API LiberFly project running locally! If you need anything else, feel free to ask.


## Endpoints And Testing

You can test the API endpoints using the Swagger documentation available at the following URL when the project is running:

[http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)

This documentation provides a user-friendly interface to interact with the API, making it easy to send requests and view responses.

Alternatively, you can use the REST Client plugin in Visual Studio Code. To do this, open the `api.rest` file in your project. This file contains a collection of predefined API requests that you can execute directly within the editor.

Using either of these methods allows you to thoroughly test the API endpoints and ensure they are functioning as expected.


### Authentication

#### Register a new user
- **POST** `/api/auth/create`
- **Parameters**:
  - `name` (string, required): User's name
  - `email` (string, required): User's email
  - `password` (string, required): User's password
  - `password_confirmation` (string, required): Confirm the password
- **Responses**:
  - `201`: User registered successfully
  - `400`: Validation errors

#### Authenticate and get token
- **POST** `/api/auth`
- **Parameters**:
  - `email` (string, required): User's email
  - `password` (string, required): User's password
- **Responses**:
  - `200`: User authenticated successfully
  - `401`: Unauthorized

#### Logout
- **POST** `/api/auth/logout`
- **Responses**:
  - `200`: Successfully logged out
  - `401`: Unauthorized

#### Refresh token
- **POST** `/api/auth/refresh`
- **Responses**:
  - `200`: Token refreshed successfully
  - `401`: Unauthorized

#### Get logged-in user details
- **GET** `/api/auth/user`
- **Security**: Bearer token required
- **Responses**:
  - `200`: User details

### Todo Lists

#### Get all todo lists
- **GET** `/api/todo`
- **Security**: Bearer token required
- **Responses**:
  - `200`: List of todo lists

#### Create a new todo list
- **POST** `/api/todo`
- **Parameters**:
  - `name` (string, required): Title of the todo list
- **Security**: Bearer token required
- **Responses**:
  - `201`: Todo list created successfully
  - `400`: Validation errors

#### Get a todo list by ID
- **GET** `/api/todo/{id}`
- **Parameters**:
  - `id` (integer, required): ID of the todo list
- **Security**: Bearer token required
- **Responses**:
  - `200`: Todo list details
  - `404`: Todo list not found

#### Update a todo list
- **PUT** `/api/todo/{id}`
- **Parameters**:
  - `id` (integer, required): ID of the todo list
  - `name` (string, required): New title of the todo list
- **Security**: Bearer token required
- **Responses**:
  - `200`: Todo list updated successfully
  - `404`: Todo list not found
  - `400`: Validation errors

#### Delete a todo list
- **DELETE** `/api/todo/{id}`
- **Parameters**:
  - `id` (integer, required): ID of the todo list
- **Security**: Bearer token required
- **Responses**:
  - `204`: Todo list deleted successfully
  - `404`: Todo list not found

### Tasks

#### Get all tasks for a todo list
- **GET** `/api/todo/{todo_id}/tasks`
- **Parameters**:
  - `todo_id` (integer, required): ID of the todo list
- **Security**: Bearer token required
- **Responses**:
  - `200`: List of tasks
  - `400`: Validation errors

#### Create a task for a todo list
- **POST** `/api/todo/{todo_id}/tasks`
- **Parameters**:
  - `todo_id` (integer, required): ID of the todo list
- **Request Body** (JSON):
  - `title` (string, required): Title of the task
  - `description` (string, optional): Task description
- **Security**: Bearer token required
- **Responses**:
  - `201`: Task created successfully
  - `400`: Validation errors

#### Get a task by ID
- **GET** `/api/todo/{todo_id}/tasks/{task_id}`
- **Parameters**:
  - `todo_id` (integer, required): ID of the todo list
  - `task_id` (integer, required): ID of the task
- **Security**: Bearer token required
- **Responses**:
  - `200`: Task details
  - `404`: Task not found

#### Update a task
- **PUT** `/api/todo/{todo_id}/tasks/{task_id}`
- **Parameters**:
  - `todo_id` (integer, required): ID of the todo list
  - `task_id` (integer, required): ID of the task
- **Request Body** (JSON):
  - `title` (string, required): New title of the task
  - `description` (string, optional): New description of the task
  - `completed` (boolean, optional): Task completion status
- **Security**: Bearer token required
- **Responses**:
  - `200`: Task updated successfully
  - `404`: Task not found
  - `400`: Validation errors

#### Delete a task
- **DELETE** `/api/todo/{todo_id}/tasks/{task_id}`
- **Parameters**:
  - `todo_id` (integer, required): ID of the todo list
  - `task_id` (integer, required): ID of the task
- **Security**: Bearer token required
- **Responses**:
  - `204`: Task deleted successfully
  - `404`: Task not found

## Security

All endpoints (except for registering and logging in) are protected by JWT-based authentication. You must provide a valid token in the `Authorization` header to access these endpoints.
