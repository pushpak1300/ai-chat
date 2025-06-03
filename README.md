![prism](https://github.com/user-attachments/assets/e97667bc-1335-48f1-8c23-474d3f31f49a)

A modern AI chat starter kit built with Laravel, featuring real-time streaming responses using Prism, Inertia.js, Vue.js, and TailwindCSS.

## Introduction

Prism Chat provides a solid foundation for building AI-powered chat applications with Laravel. It leverages Laravel's `useStream()` functionality to deliver real-time streaming responses, creating a dynamic and engaging user experience.

## Features

- **Real-time AI Responses**: Stream AI responses as they're generated
- **Multiple Models Support**: Select from various AI models
- **Authentication System**: Built-in user authentication and management
- **Responsive UI**: Modern interface that works across devices
- **Appearance Settings**: Light/dark mode support

## Tech Stack

- **Backend**: Laravel
- **Frontend**: Vue.js, Inertia.js
- **Styling**: TailwindCSS
- **Database**: SQLite (configurable)

## Setup

### Prerequisites

- PHP 8.2+
- Composer
- Node.js and npm/bun
- Laravel CLI

### Installation

Installation can be done by laravel installer.

```bash
laravel new --using=pushpak1300/ai-chat ai-chat
```

Or Normal Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/pushpak1300/ai-chat.git
   cd ai-chat
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install JavaScript dependencies:
   ```bash
   npm install
   # or
   bun install
   ```

4. Create a copy of the environment file:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Set up your database connection in `.env`

7. Run migrations:
   ```bash
   php artisan migrate
   ```

8. Seed the database (optional):
   ```bash
   php artisan db:seed
   ```

9. Start the development server:
   ```bash
   php artisan serve
   ```

10. Compile assets:
    ```bash
    npm run dev
    # or
    bun run dev
    ```


## Configuration

Configure your AI provider settings in the `config/prism.php` file or through the `.env` file:

```
GEMINI_API_KEY=your_api_key
```

## Usage

After installation, navigate to `http://localhost:8000` in your web browser and register a new account to start using the chat application.


## Security

> **Warning**: Never commit sensitive data like API keys or credentials to your repository.

If you discover any security vulnerabilities, please send an email to [pushpak1300@gmail.com] instead of using the issue tracker.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
