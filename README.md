# CRM AI Platform

## Getting Started

This is a Laravel + Vue (Inertia.js) project. Follow the steps below to easily start the project in the future.

### Prerequisites
- Node.js & npm
- PHP >= 8.2 & Composer
- SQLite (or your preferred database, configured in `.env`)

### Setup Instructions (First Time)
1. **Environment File**: 
   Copy the example `.env` file to create your own configuration.
   ```bash
   cp .env.example .env
   ```
2. **Install PHP Dependencies**:
   ```bash
   composer install
   ```
3. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```
4. **Database Migrations**:
   Run the migrations and seeders to set up your database structure.
   ```bash
   php artisan migrate --seed
   ```
5. **Install Node Modules**:
   ```bash
   npm install
   ```

### Starting the Application (Daily Workflow)
To run the frontend and backend simultaneously in development mode, run:

```bash
npm run dev
# OR if using the composer defined script:
composer run dev
```

For the frontend only (vite dev server):
```bash
npm run dev
```
For the backend server only:
```bash
php artisan serve
```

---
**Note**: This repository is proprietary. Review the `LICENSE` file for usage restrictions.
