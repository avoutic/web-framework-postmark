<?php

/*
 * This file is part of WebFramework.
 *
 * (c) Avoutic <avoutic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WebFramework\Postmark;

use Postmark\PostmarkClient;

/**
 * Factory class for creating Postmark API clients.
 */
class PostmarkClientFactory
{
    /** The default timeout in seconds */
    public const DEFAULT_TIMEOUT = 5;

    /** @var null|PostmarkClient The cached Postmark client instance */
    private ?PostmarkClient $client = null;

    /**
     * PostmarkClientFactory constructor.
     *
     * @param string $apiKey  The Postmark API key
     * @param int    $timeout The timeout in seconds of the client returned by getClient()
     */
    public function __construct(
        private string $apiKey,
        private int $timeout = self::DEFAULT_TIMEOUT,
    ) {}

    /**
     * Get a Postmark client instance.
     *
     * This method caches the client instance for reuse. The client uses the timeout
     * the factory was constructed with.
     *
     * @return PostmarkClient The Postmark client
     */
    public function getClient(): PostmarkClient
    {
        if ($this->client === null)
        {
            $this->client = $this->createClient($this->timeout);
        }

        return $this->client;
    }

    /**
     * Create a new Postmark client instance with its own timeout.
     *
     * Use this for calls that need more time than sending mail, such as exporting
     * suppressions. The client is not cached.
     *
     * @param int $timeout The timeout in seconds
     *
     * @return PostmarkClient The Postmark client
     */
    public function createClient(int $timeout): PostmarkClient
    {
        if ($timeout < 1)
        {
            throw new \InvalidArgumentException('Timeout must be at least 1 second');
        }

        return new PostmarkClient($this->apiKey, $timeout);
    }
}
