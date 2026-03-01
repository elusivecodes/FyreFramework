<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Traits;

use Fyre\Mail\MailManager;
use Fyre\TestSuite\Constraint\Email\MailBodyContains;
use Fyre\TestSuite\Constraint\Email\MailContainsAttachment;
use Fyre\TestSuite\Constraint\Email\MailCount;
use Fyre\TestSuite\Constraint\Email\MailSentFrom;
use Fyre\TestSuite\Constraint\Email\MailSentTo;
use Fyre\TestSuite\Constraint\Email\MailSentWith;
use Fyre\TestSuite\Constraint\Email\MailSubjectContains;
use Fyre\TestSuite\Constraint\Email\NoMailSent;
use Fyre\TestSuite\Mail\Handlers\TestMailer;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

/**
 * Test case helpers for mail assertions.
 *
 * These helpers configure the mailer(s) to use a test handler and provide assertion
 * wrappers around sent-message constraints.
 */
trait EmailTestTrait
{
    protected MailManager $mailManager;

    /**
     * Assert that an email body contains a string.
     *
     * @param string $needle The expected body string.
     * @param string $message The message to display on failure.
     */
    public function assertMailContains(string $needle, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages(),
            new MailBodyContains($needle),
            $message
        );
    }

    /**
     * Assert that a specific email body contains a string.
     *
     * @param string $needle The expected body string.
     * @param int $at The index of the email (1-based).
     * @param string $message The message to display on failure.
     */
    public function assertMailContainsAt(string $needle, int $at, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages($at),
            new MailBodyContains($needle, at: $at),
            $message
        );
    }

    /**
     * Assert that an email contains an attachment.
     *
     * @param string $filename The expected filename.
     * @param string $message The message to display on failure.
     */
    public function assertMailContainsAttachment(string $filename, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages(),
            new MailContainsAttachment($filename),
            $message
        );
    }

    /**
     * Assert that a specific email contains an attachment.
     *
     * @param string $filename The expected filename.
     * @param int $at The index of the email (1-based).
     * @param string $message The message to display on failure.
     */
    public function assertMailContainsAttachmentAt(string $filename, int $at, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages($at),
            new MailContainsAttachment($filename, $at),
            $message
        );
    }

    /**
     * Assert that an email HTML body contains a string.
     *
     * @param string $needle The expected HTML body string.
     * @param string $message The message to display on failure.
     */
    public function assertMailContainsHtml(string $needle, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages(),
            new MailBodyContains($needle, 'html'),
            $message
        );
    }

    /**
     * Assert that a specific email HTML body contains a string.
     *
     * @param string $needle The expected HTML body string.
     * @param int $at The index of the email (1-based).
     * @param string $message The message to display on failure.
     */
    public function assertMailContainsHtmlAt(string $needle, int $at, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages($at),
            new MailBodyContains($needle, 'html', $at),
            $message
        );
    }

    /**
     * Assert that an email text body contains a string.
     *
     * @param string $needle The expected text body string.
     * @param string $message The message to display on failure.
     */
    public function assertMailContainsText(string $needle, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages(),
            new MailBodyContains($needle, 'text'),
            $message
        );
    }

    /**
     * Assert that a specific email text body contains a string.
     *
     * @param string $needle The expected text body string.
     * @param int $at The index of the email (1-based).
     * @param string $message The message to display on failure.
     */
    public function assertMailContainsTextAt(string $needle, int $at, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages($at),
            new MailBodyContains($needle, 'text', $at),
            $message
        );
    }

    /**
     * Assert that a number of emails were sent.
     *
     * @param int $count The expected number of emails sent.
     * @param string $message The message to display on failure.
     */
    public function assertMailCount(int $count, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages(),
            new MailCount($count),
            $message
        );
    }

    /**
     * Assert that an email was sent from an address.
     *
     * @param string $address The expected from address.
     * @param string $message The message to display on failure.
     */
    public function assertMailSentFrom(string $address, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages(),
            new MailSentFrom($address),
            $message
        );
    }

    /**
     * Assert that a specific email was sent from an address.
     *
     * @param string $address The expected from address.
     * @param int $at The index of the email (1-based).
     * @param string $message The message to display on failure.
     */
    public function assertMailSentFromAt(string $address, int $at, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages($at),
            new MailSentFrom($address, $at),
            $message
        );
    }

    /**
     * Assert that an email was sent to an address.
     *
     * @param string $address The expected to address.
     * @param string $message The message to display on failure.
     */
    public function assertMailSentTo(string $address, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages(),
            new MailSentTo($address),
            $message
        );
    }

    /**
     * Assert that a specific email was sent to an address.
     *
     * @param string $address The expected to address.
     * @param int $at The index of the email (1-based).
     * @param string $message The message to display on failure.
     */
    public function assertMailSentToAt(string $address, int $at, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages($at),
            new MailSentTo($address, $at),
            $message
        );
    }

    /**
     * Assert that an email was sent to a BCC address.
     *
     * @param string $address The expected BCC address.
     * @param string $message The message to display on failure.
     */
    public function assertMailSentWithBcc(string $address, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages(),
            new MailSentWith($address, 'bcc'),
            $message
        );
    }

    /**
     * Assert that a specific email was sent to a BCC address.
     *
     * @param string $address The expected BCC address.
     * @param int $at The index of the email (1-based).
     * @param string $message The message to display on failure.
     */
    public function assertMailSentWithBccAt(string $address, int $at, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages($at),
            new MailSentWith($address, 'bcc', $at),
            $message
        );
    }

    /**
     * Assert that an email was sent to a CC address.
     *
     * @param string $address The expected CC address.
     * @param string $message The message to display on failure.
     */
    public function assertMailSentWithCc(string $address, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages(),
            new MailSentWith($address, 'cc'),
            $message
        );
    }

    /**
     * Assert that a specific email was sent to a CC address.
     *
     * @param string $address The expected CC address.
     * @param int $at The index of the email (1-based).
     * @param string $message The message to display on failure.
     */
    public function assertMailSentWithCcAt(string $address, int $at, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages($at),
            new MailSentWith($address, 'cc', $at),
            $message
        );
    }

    /**
     * Assert that an email was sent with a reply-to address.
     *
     * @param string $address The expected reply-to address.
     * @param string $message The message to display on failure.
     */
    public function assertMailSentWithReplyTo(string $address, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages(),
            new MailSentWith($address, 'reply-to'),
            $message
        );
    }

    /**
     * Assert that a specific email was sent with a reply-to address.
     *
     * @param string $address The expected reply-to address.
     * @param int $at The index of the email (1-based).
     * @param string $message The message to display on failure.
     */
    public function assertMailSentWithReplyToAt(string $address, int $at, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages($at),
            new MailSentWith($address, 'reply-to', $at),
            $message
        );
    }

    /**
     * Assert that an email was sent with a sender address.
     *
     * @param string $address The expected sender address.
     * @param string $message The message to display on failure.
     */
    public function assertMailSentWithSender(string $address, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages(),
            new MailSentWith($address, 'sender'),
            $message
        );
    }

    /**
     * Assert that a specific email was sent with a sender address.
     *
     * @param string $address The expected sender address.
     * @param int $at The index of the email (1-based).
     * @param string $message The message to display on failure.
     */
    public function assertMailSentWithSenderAt(string $address, int $at, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages($at),
            new MailSentWith($address, 'sender', $at),
            $message
        );
    }

    /**
     * Assert that an email subject contains a string.
     *
     * @param string $needle The expected subject string.
     * @param string $message The message to display on failure.
     */
    public function assertMailSubjectContains(string $needle, string $message = ''): void
    {
        $this->assertThat(
            TestMailer::getMessages(),
            new MailSubjectContains($needle),
            $message
        );
    }

    /**
     * Assert that a specific email subject contains a string.
     *
     * @param string $needle The expected subject string.
     * @param int $at The index of the email (1-based).
     * @param string $message The message to display on failure.
     */
    public function assertMailSubjectContainsAt(string $needle, int $at, string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages($at),
            new MailSubjectContains($needle, $at),
            $message
        );
    }

    /**
     * Assert that no emails were sent.
     *
     * @param string $message The message to display on failure.
     */
    public function assertNoMailSent(string $message = ''): void
    {
        $this->assertThat(
            $this->getMessages(),
            new NoMailSent(),
            $message
        );
    }

    /**
     * Returns the sent emails to check.
     *
     * @param int|null $at The email message index (1-based).
     * @return array The sent emails.
     */
    public function getMessages(int|null $at = null): array
    {
        $messages = TestMailer::getMessages();

        if ($at === null) {
            return $messages;
        }

        $index = $at - 1;

        if (!isset($messages[$index])) {
            return [];
        }

        return [$messages[$index]];
    }

    /**
     * Clear messages.
     */
    #[After]
    protected function clearMessages(): void
    {
        TestMailer::clearMessages();
    }

    /**
     * Set up mail handlers.
     */
    #[Before(-1)]
    protected function setupMailHandlers(): void
    {
        $this->mailManager = $this->app->use(MailManager::class);
        $configs = $this->mailManager->getConfig();
        $this->mailManager->clear();

        foreach ($configs as $key => $config) {
            $config['className'] = TestMailer::class;
            $this->mailManager->setConfig($key, $config);
        }
    }
}
