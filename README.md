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

## Configuration

If you are using the definition from _definitions/defitinions.php_. You can just add the following _postmark.php_ to your auth config directory (_config/auth_):

```php
<?php

return 'your-api-key-here';
```

## Usage

The module provides a `PostmarkMailService` that implements the Web Framework's `MailService` interface. It will be automatically configured via dependency injection.

## License

MIT License - see LICENSE file for details 