<?php

namespace WebFramework;

use DI;
use Psr\Container\ContainerInterface;
use WebFramework\Security\ConfigService;

return [
    Postmark\PostmarkClientFactory::class => DI\factory(function (ContainerInterface $c) {
        $secureConfigService = $c->get(ConfigService::class);

        $apiKey = $secureConfigService->getAuthConfig('postmark');

        return new Postmark\PostmarkClientFactory($apiKey);
    }),
    Postmark\PostmarkMailService::class => DI\autowire()
        ->constructorParameter('defaultSender', DI\get('sender_core.default_sender')),
];
