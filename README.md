# Todo Application

Welcome to the **Todo Application**. This project combines a **Vue.js** frontend with a **Laravel** backend, orchestrated using **Docker** and **Nginx** for seamless deployment and management. The application allows users to search for news articles from various sources, view them in an intuitive interface, and navigate through paginated results.

---

## Table of Contents

1. [Technologies Used](#technologies-used)
2. [Repository Structure](#repository-structure)
3. [Prerequisites](#prerequisites)
4. [Getting Started](#getting-started)
   - [1. Clone the Repository](#1-clone-the-repository)
   - [2. Environment Configuration](#2-environment-configuration)
   - [3. Docker Setup](#3-docker-setup)
5. [Cron Job Setup](#cron-job-setup)
6. [Running the Application](#running-the-application)
7. [Contact](#contact)

---

## Technologies Used

- **Frontend:**
  - Vue.js
  - Axios
  - Vue Router
  - CSS
  - Docker

- **Backend:**
  - Laravel 11
  - Composer
  - MySQL
  - Docker

- **DevOps:**
  - Docker
  - Docker Compose
  - Nginx

---

## Repository Structure

- **backend/**: Laravel backend application.
- **frontend/**: Vue.js frontend application.
- **nginx/**: Nginx configuration files.
- **docker-compose.yml**: Docker services configuration.
- **.gitignore**: Files and folders to ignore in Git.
- **README.md**: This documentation file.

---

## Prerequisites

Before setting up the project, ensure you have the following installed on your computer:

- **[Git](https://git-scm.com/downloads)**: For version control.
- **[Docker](https://www.docker.com/get-started)**: For containerization.
- **[Docker Compose](https://docs.docker.com/compose/install/)**: To run multi-container Docker applications.
- **[Node.js & npm/Yarn](https://nodejs.org/en/download/)**: JavaScript runtime and package manager (if not using Docker for frontend).
- **[Composer](https://getcomposer.org/download/)**: PHP dependency manager (if not using Docker for backend).

> **Note:** This guide assumes you are using Docker for both frontend and backend. If you prefer to run services without Docker, ensure Node.js, npm/Yarn, and Composer are installed.

---

## Getting Started

### 1. Clone the Repository

Clone this repository to your local machine using Git:

```bash
git clone https://gitlab.com/appetiser/appetiser-pre-hire-coding-challenge/11062024-sufyan-ejaz-web.git
cd 11062024-sufyan-ejaz-web
```

### 2. Environment Configuration

Navigate to the backend folder:(backend/.env)

```bash
cd backend
```

Copy the example .env file if its not already exist:

```bash
cp .env.example .env
```

Open the .env file and set your environment variables, especially the API keys (NEWSAPI_KEY, GUARDIAN_API_KEY, NYTIMES_API_KEY),
also set QUEUE_CONNECTION to database

```bash
APP_NAME=Todo
APP_ENV=local
APP_KEY=base64:YourGeneratedAppKey
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=todo_app
DB_USERNAME=root
DB_PASSWORD=secret

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=redis
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Other environment variables...

```

Run this command to generate app key in .env if already not exist

```bash
php artisan key:generate
```
### 3. Docker Setup

a. Build and Start Containers

```bash
docker-compose up --build -d
```
- -d: Runs containers in the background
- --build: Rebuilds the Docker images.

b. Check if Containers are Running

```bash
docker-compose ps
```
c. Make sure you should see containers running in Up state:

- backend 
- nginx_server
- frondend 
- mysql
- laravel_cron
- phpmyadmin

## Cron Job Setup

The backend has a cron job to delete archived tasks after a week automatically.

## Running the Application

After setting up complete project, and ensuring Docker containers are running, follow these steps:

a. Access the Application

- Open http://localhost:3000 in your browser. You should see the Todo interface served by Vue app.
- Open http://localhost:8000/docs/ in your browser to access restful apis (Laravel) end points.

b. Search for Tasks by title, description, due date, completed date, archived date

- Use the search bar to enter keywords.

c. View Articles

- Tasks are displayed in a card layout.
- Use Previous and Next buttons to navigate through pages.

d. Authentication

- Ensure authentication tokens are stored in localStorage for authorized API requests.

## Contact

If you have any questions or suggestions, please contact:

- Name: Sufyan Ejaz
- Email: sufyan.ejaz.266@gmail.com
- LinkedIn: https://www.linkedin.com/in/sufyanejazofficial