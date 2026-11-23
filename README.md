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

### Timeout

The Postmark client used for sending mail has a timeout of 5 seconds. To change it, construct the `PostmarkClientFactory` with another timeout in your own definition:

```php
<?php

use Psr\Container\ContainerInterface;
use WebFramework\Postmark\PostmarkClientFactory;
use WebFramework\Security\ConfigService;

return [
    PostmarkClientFactory::class => DI\factory(function (ContainerInterface $c) {
        $apiKey = $c->get(ConfigService::class)->getAuthConfig('postmark');

        return new PostmarkClientFactory($apiKey, timeout: 10);
    }),
];
```

For a single call that needs more time, such as exporting suppressions, ask the factory for a separate client:

```php
$client = $postmarkClientFactory->createClient(30);
```

This client uses the same API key and is not cached.

## License

MIT License - see LICENSE file for details 