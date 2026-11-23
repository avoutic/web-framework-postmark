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

use GuzzleHttp\Exception\ConnectException;
use Postmark\Models\PostmarkException;
use Postmark\PostmarkClient;
use WebFramework\Core\RuntimeEnvironment;
use WebFramework\Diagnostics\Instrumentation;
use WebFramework\Mail\MailBackend;
use WebFramework\Mail\MailService;

/**
 * Class PostmarkMailService.
 *
 * Implements the MailService interface using the Postmark email service.
 */
class PostmarkMailService implements MailService, MailBackend
{
    /**
     * PostmarkMailService constructor.
     *
     * @param Instrumentation       $instrumentation    The instrumentation service for performance tracking
     * @param PostmarkClientFactory $clientFactory      The factory for creating Postmark client instances
     * @param RuntimeEnvironment    $runtimeEnvironment The runtime environment service
     * @param string                $defaultSender      The default sender email address
     */
    public function __construct(
        private Instrumentation $instrumentation,
        private PostmarkClientFactory $clientFactory,
        private RuntimeEnvironment $runtimeEnvironment,
        private string $defaultSender,
    ) {}

    /**
     * Get the Postmark client instance.
     */
    private function getClient(): PostmarkClient
    {
        return $this->clientFactory->getClient();
    }

    /**
     * Send a raw email.
     *
     * @param null|string $from    The sender's email address (null to use default)
     * @param string      $to      The recipient's email address
     * @param string      $subject The email subject
     * @param string      $message The email body
     *
     * @return bool|string True if sent successfully, or an error message string
     *
     * @throws \RuntimeException  If no recipient or sender email is specified, or the connection to Postmark fails
     * @throws PostmarkException If Postmark reports an error other than an inactive recipient
     */
    public function sendRawMail(?string $from, string $to, string $subject, string $message): bool|string
    {
        $from = $from ?? $this->defaultSender;

        if (!strlen($to))
        {
            throw new \RuntimeException('No recipient e-mail specified');
        }

        if (!strlen($from))
        {
            throw new \RuntimeException('No source e-mail specified');
        }

        return $this->send('mail.send_raw', function () use ($from, $to, $subject, $message): void {
            $this->getClient()->sendEmail(
                $from,
                $to,
                $subject,
                null,
                $message
            );
        });
    }

    /**
     * Send an email using a template.
     *
     * @param string               $templateId        The ID of the email template to use
     * @param null|string          $from              The sender's email address (null to use default)
     * @param string               $to                The recipient's email address
     * @param array<string, mixed> $templateVariables Variables to be used in the template
     *
     * @return bool|string True if sent successfully, or an error message string
     *
     * @throws \RuntimeException  If no recipient or sender email is specified, or the connection to Postmark fails
     * @throws PostmarkException If Postmark reports an error other than an inactive recipient
     */
    public function sendTemplateMail(string $templateId, ?string $from, string $to, array $templateVariables): bool|string
    {
        $from = $from ?? $this->defaultSender;
        $replyTo = $templateVariables['reply_to'] ?? null;

        if (!strlen($to))
        {
            throw new \RuntimeException('No recipient e-mail specified');
        }

        if (!strlen($from))
        {
            throw new \RuntimeException('No source e-mail specified');
        }

        if (!isset($templateVariables['server_name']))
        {
            $templateVariables['server_name'] = $this->runtimeEnvironment->getServerName();
        }

        try
        {
            return $this->send('mail.send_template', function () use ($from, $to, $templateId, $templateVariables, $replyTo): void {
                $this->getClient()->sendEmailWithTemplate(
                    $from,
                    $to,
                    $templateId,
                    $templateVariables,
                    true,               // inlineCSS
                    null,               // tag
                    true,               // trackOpens
                    $replyTo           // replyTo
                );
            });
        }
        catch (PostmarkException $e)
        {
            if ($e->PostmarkApiErrorCode == 1101)
            {
                throw new \RuntimeException("Template ID {$templateId} not correct", previous: $e);
            }

            throw $e;
        }
    }

    /**
     * Run a send call with the error handling shared by raw and template mail.
     *
     * @param string         $spanOperation The operation name for the instrumentation span
     * @param callable():void $send          The call that sends the mail
     *
     * @return bool|string True if sent successfully, or 'inactive_address' if the recipient is inactive
     *
     * @throws \RuntimeException  If the connection to Postmark fails
     * @throws PostmarkException If Postmark reports another error
     */
    private function send(string $spanOperation, callable $send): bool|string
    {
        $span = $this->instrumentation->startSpan($spanOperation);

        try
        {
            $send();
        }
        catch (ConnectException $e)
        {
            throw new \RuntimeException('Postmark Connection failure', previous: $e);
        }
        catch (PostmarkException $e)
        {
            // Postmark refuses to send to an inactive recipient (bounced or marked as spam)
            //
            if ($e->PostmarkApiErrorCode == 406)
            {
                return 'inactive_address';
            }

            throw $e;
        }
        finally
        {
            $this->instrumentation->finishSpan($span);
        }

        return true;
    }
}
