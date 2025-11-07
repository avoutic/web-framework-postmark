# Web Framework Postmark Module

This module provides Postmark email service integration for WebFramework.

## Features

- Send raw emails via Postmark
- Send template-based emails via Postmark
- Automatic instrumentation for email sending
- Configurable default sender email
- Error handling for common Postmark API issues

## Requirements

- PHP 8.2 or higher
- Web Framework 8.x
- Postmark API key

## Installation

Install via Composer:

```bash
composer require avoutic/web-framework-postmark
```

Make sure the definitions file is included in your project by adding it to `definition_files` in your `config.php` file:

```php
return [
    'definition_files' => [
        '../vendor/avoutic/web-framework/definitions/definitions.php',
        '../vendor/avoutic/web-framework-postmark/definitions/definitions.php',
        'app_definitions.php',
    ],
];
```

## Usage

The module provides Postmark integration for WebFramework, enabling email sending. It implements the WebFramework MailService and MailBackend interfaces to send emails.

To use Postmark for email sending you need to add it in your PHP-DI definitions:

```php
return [
    MailService::class => DI\autowire(PostmarkMailService::class),
    MailBackend::class => DI\autowire(PostmarkMailService::class),
];
```

## Configuration

If you are using the definition from _definitions/defitinions.php_. You can just add the following _postmark.php_ to your auth config directory (_config/auth_):

```php
<?php

return 'your-api-key-here';
```

## License

MIT License - see LICENSE file for details 