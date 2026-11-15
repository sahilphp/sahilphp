<?php

declare(strict_types=1);

namespace Tests\Mail;

use PHPUnit\Framework\TestCase;
use Sahil\Contracts\Mail\MailerContract;
use Sahil\Foundation\Application;
use Sahil\Mail\Mail;
use Sahil\Mail\MailManager;
use Sahil\Mail\Mailable;
use Sahil\Mail\Message;
use Sahil\Notifications\MailMessage;

final class StarterWelcomeMail extends Mailable
{
    public function build(): void
    {
        $this->subject('Welcome aboard')
            ->html('<h1>Karibu!</h1>')
            ->text('Karibu!');
    }
}

/**
 * `Mail::` against the real booted application: container-bound
 * MailManager, the configured default transport, fakes that intercept the
 * binding, and notification MailMessage attachment passthrough.
 */
final class MailFlowTest extends TestCase
{
    protected function tearDown(): void
    {
        Mail::flushState();
        Application::free();
        parent::tearDown();
    }

    public function test_container_binds_the_manager_and_contract(): void
    {
        $app = $this->boot();

        $mailer = $app->container()->make(MailerContract::class);
        self::assertInstanceOf(MailManager::class, $mailer);
        self::assertSame($mailer, $app->container()->make('mailer'));
        self::assertSame($mailer, $app->container()->make(MailManager::class));
    }

    public function test_send_through_the_configured_default_transport(): void
    {
        $app = $this->boot();

        // config/mail.php defaults MAIL_TRANSPORT=log — a safe dev default.
        $receipt = Mail::to('dev@example.com')
            ->subject('smoke')
            ->text('hello')
            ->send();

        self::assertFalse($receipt->status->isFailure());
    }

    public function test_fake_intercepts_facade_and_injected_mailer(): void
    {
        $app = $this->boot();
        Mail::fake();

        // The container binding was swapped too.
        $resolved = $app->container()->make(MailerContract::class);
        self::assertInstanceOf(\Sahil\Mail\FakeMailer::class, $resolved);

        Mail::to('ada@example.com')->send(new StarterWelcomeMail());
        Mail::to('grace@example.com')->subject('q')->text('t')->queue();

        Mail::assertSent(StarterWelcomeMail::class);
        Mail::assertQueued();
        self::assertSame('Welcome aboard', Mail::sent()[0]->message->subjectLine());
        self::assertSame('ada@example.com', Mail::sent()[0]->message->toAddresses()[0]->email);

        // flushState restores the real manager.
        Mail::flushState();
        self::assertInstanceOf(MailManager::class, $app->container()->make(MailerContract::class));
    }

    public function test_notification_message_carries_attachments(): void
    {
        $this->boot();

        $message = MailMessage::make()
            ->subject('Invoice')
            ->line('Attached.')
            ->attachData('PDF', 'invoice.pdf', 'application/pdf')
            ->toMessage();

        self::assertCount(1, $message->attachments());
        self::assertSame('invoice.pdf', $message->attachments()[0]->name);
    }

    private function boot(): Application
    {
        require_once dirname(__DIR__, 2).'/bootstrap/app.php';

        $app = \sahil_bootstrap();

        if (Application::peek() !== $app) {
            $configured = new \ReflectionProperty(Application::class, 'configured');
            $configured->setValue(null, $app);
        }

        return $app;
    }
}
