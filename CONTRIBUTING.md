# Contributing to TV Channel Monitoring System

Thank you for considering contributing to the TV Channel Monitoring System! This document outlines the guidelines for contributing to this project.

## Code of Conduct

By participating in this project, you agree to abide by our Code of Conduct. Please be respectful and considerate of others.

## How Can I Contribute?

### Reporting Bugs

- Check if the bug has already been reported in the Issues section
- Use the bug report template when creating a new issue
- Include detailed steps to reproduce the bug
- Provide information about your environment (OS, browser, etc.)
- Include screenshots if applicable

### Suggesting Features

- Check if the feature has already been suggested in the Issues section
- Use the feature request template when creating a new issue
- Clearly describe the feature and its benefits
- Provide examples of how the feature would be used

### Pull Requests

1. Fork the repository
2. Create a new branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests to ensure your changes don't break existing functionality
5. Commit your changes (`git commit -m 'Add some amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

## Development Setup

### Prerequisites

- PHP 8.2+
- Node.js 18+
- FFmpeg 5.0+
- MySQL 8.0+ or PostgreSQL 14+
- Redis 6.0+
- Composer
- npm

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/tv-monitor.git
   cd tv-monitor
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install JavaScript dependencies:
   ```bash
   npm install
   ```

4. Copy the environment file and configure it:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Configure your database in the `.env` file

6. Run migrations:
   ```bash
   php artisan migrate
   ```

7. Build frontend assets:
   ```bash
   npm run dev
   ```

8. Start the development server:
   ```bash
   php artisan serve
   ```

### Testing

Run the test suite to ensure your changes don't break existing functionality:

```bash
php artisan test
```

For frontend tests:

```bash
npm run test
```

## Coding Standards

### PHP

- Follow PSR-12 coding standards
- Use type hints for method parameters and return types
- Write PHPDoc comments for classes and methods
- Use Laravel's built-in features and conventions

### JavaScript/Vue.js

- Follow the Vue.js style guide
- Use ES6+ features
- Use Vue's composition API for new components
- Write JSDoc comments for functions and components

### CSS

- Use BEM methodology for class naming
- Use SCSS for styling
- Keep styles modular and component-specific

## Documentation

- Update documentation when adding or modifying features
- Document public APIs and components
- Keep code comments up-to-date

## Commit Messages

- Use clear and descriptive commit messages
- Start with a verb in the present tense (e.g., "Add", "Fix", "Update")
- Reference issue numbers when applicable

## License

By contributing to this project, you agree that your contributions will be licensed under the project's MIT License. 