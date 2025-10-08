<div align="center">
  <img src="./public/logos/icon.png" width="200" alt="knino" />

  <h1>K nino</h1>

  <p>Sistema de procesamiento de transacciones</p>
  <a href="" target="_blank">Live Preview</a>
  <span>&nbsp;•&nbsp;</span>
  <a href="#getting-started">Getting Started</a>
</div>

<div align="center">
  <img alt="Laravel Badge" src="https://img.shields.io/badge/Laravel-FF2D20?logo=laravel&logoColor=fff&style=flat" />
  <img alt="Bun Badge" src="https://img.shields.io/badge/Bun-000000?logo=bun&logoColor=fff&style=flat" />
  <img alt="Docker Badge" src="https://img.shields.io/badge/Docker-2496ED?logo=docker&logoColor=fff&style=flat" />
  <img alt="PostgreSQL Badge" src="https://img.shields.io/badge/PostgreSQL-4169E1?logo=postgresql&logoColor=fff&style=flat" />
  <img alt="JWT Badge" src="https://img.shields.io/badge/JWT-000000?logo=json-web-tokens&logoColor=fff&style=flat" />
  <img alt="Tailwind CSS Badge" src="https://img.shields.io/badge/Tailwind%20CSS-06B6D4?logo=tailwindcss&logoColor=fff&style=flat" />
  <img alt="Eloquent Badge" src="https://img.shields.io/badge/Eloquent-FF2D20?logo=laravel&logoColor=fff&style=flat" />
</div>

# 📝 Overview

## Features

- 🔒 Secure authentication with Google
- CRUD for clients, pets, services and employees
- Upload Images to cloudinary
- ⚡ Fast, responsive UI with Tailwind CSS

# 🛠️ Tech Stack

- [**Laravel**](https://laravel.com/) - The PHP framework for web artisans. Elegant, expressive syntax for rapid development.
- [**PHP**](https://www.php.net/) - A popular general-purpose scripting language that is especially suited to web development.
- [**PostgreSQL**](https://www.postgresql.org/) - Systems of manager for object-relational database.
- [**Tailwind**](https://tailwindcss.com/) - A utility-first CSS framework for rapidly building custom designs.
- **Eloquent** – Modern ORM for type-safe database access
- **JWT** – Secure authentication and user management
- **Bun** – Fast JavaScript runtime and package manager
- [**Tabler Icons**](https://tabler.io/) - A collection of icons used.

# 🎨 Live Preview

Curious? Explore the live site: <http://localhost:5173/>

# 🚀 Getting Started for local environment

Set up the project locally in a few simple steps:

### 1. Clone the repository

```bash
git clone https://github.com/angelcruz07/CodeQuest2025.git

cd CodeQuest2025
```

### 2. Install dependencies

> _We use [bun](https://bun.sh) for blazing-fast installs._

```bash
bun i
```

### 3. Configure environment variables

Copy the template and fill in your secrets:

> [!IMPORTANT]
> You need Discord credentials, and Cloudinary Credentials
> for running this project

```bash
cp .env.template .env
```

> [!WARNING]
> Check if your credentials already exist

### 4. Start the database

> [!NOTE]
> Docker is required for local database setup.

```bash
docker compose up -d
```

### 5. Run database migrations

```bash
bunx prisma migrate dev
```

### 6. Seed the database

```bash
bun seed
```

### 7. Cloudinary Setup

Devtalles team you can use out cloudinary credentials
there are in our discord channel #code-verse.

If you prefer to use your own account, you can create a free account
and create a foldeer named `codequest25/posts` to store the images.

> [!IMPORTANT]
> If you use your own account, no forget create the folder "codequest25/posts"

### 8. Discord Auth Setup

Devtalles team you can use out discord credentials
there are in our discord channel #code-verse.

If you prefer to use your own account, follow these steps:
create a new application in the Discord Developer Portal, and add the following URL to the OAuth2 Redirects:

```bash
# For local development
http://localhost:4321/api/auth/callback/discord

# Production
https://yourdomain.com/api/auth/callback/discord
```

### 9. Start the development server

```bash
bun dev
```

Visit [http://localhost:4321](http://localhost:4321/) to see DevTalles Community Blog in action.

### 8. Set up admin access

> [!NOTE]
> This step is optional and only required if you want to access admin features.

To access the dashboard and admin features:

1. **Create a user account** by registering through the application
2. **Ensure Docker container is running** (from step 4)
3. **Connect to the database** using a database client like:
   - [TablePlus](https://tableplus.com/)
   - [DBeaver](https://dbeaver.io/)
   - [pgAdmin](https://www.pgadmin.org/)
   - Or any PostgreSQL client of your choice

4. **Update user role**:
   - Connect to your PostgreSQL database
   - Navigate to the `User` table
   - Find your user record
   - Change the `role` field from `user` to `admin`
   - Save the changes

5. **Refresh the application** and log in to access admin features

Happy coding! 🚀

## Contributors

- [Angel - Frontend](https://github.com/angelcruz07)
- [Lizandro - Backend](https://github.com/LizandroBackEnd)
- [Arif - Frontend](https://github.com/Ariff-dev)
