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

- PHP 8.3+
- Composer
- Node.js and npm/bun
- SQLite

### Installation

Installation can be done by laravel installer.

```bash
laravel new --using=pushpak1300/ai-chat ai-chat
```

Or follow normal laravel installation process.

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
