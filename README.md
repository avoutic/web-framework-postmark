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
- WebFramework core package (^12)
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

## Upgrading to WebFramework 12

* `sendRawMail()` now uses the given sender. Before, it always sent from the default sender, whatever was passed as `$from`. Callers that pass a `$from` now really send from that address, so it must be a sender that is verified in your Postmark account. Pass `null` to use the default sender.
* `sendRawMail()` now returns `'inactive_address'` when Postmark refuses an inactive recipient (error 406), as `sendTemplateMail()` already did. Before, it threw a `PostmarkException`.
* Failures are now reported as a `WebFramework\Exception\MailException` (a `\RuntimeException`), so callers do not depend on Postmark's classes. `getErrorCode()` gives the Postmark API error code and `getPrevious()` the original exception. This covers a failed connection (before: a `\RuntimeException` for raw mail and the Guzzle `ConnectException` for template mail), a wrong template id (before: a `\RuntimeException`) and every other Postmark error, such as an invalid API token (before: a `PostmarkException`). Code that catches `PostmarkException` or `ConnectException` around these calls must catch `MailException` instead.

## License

MIT License - see LICENSE file for details 