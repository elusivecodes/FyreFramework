<?php
declare(strict_types=1);

namespace Fyre\Mail\Handlers;

use Fyre\Core\Attributes\SensitivePropertyArray;
use Fyre\Mail\Email;
use Fyre\Mail\Exceptions\MailException;
use Fyre\Mail\Mailer;
use Override;

use function array_key_first;
use function base64_encode;
use function fclose;
use function fgets;
use function fwrite;
use function is_resource;
use function preg_replace;
use function sprintf;
use function str_starts_with;
use function stream_context_create;
use function stream_set_timeout;
use function stream_socket_client;
use function stream_socket_enable_crypto;
use function strlen;
use function substr;

use const STREAM_CLIENT_CONNECT;
use const STREAM_CRYPTO_METHOD_TLS_CLIENT;

/**
 * Sends mail via SMTP.
 */
class SmtpMailer extends Mailer
{
    /**
     * @var array<string, mixed>
     */
    #[Override]
    protected static array $defaults = [
        'host' => '127.0.0.1',
        'username' => null,
        'password' => null,
        'port' => '465',
        'auth' => false,
        'tls' => false,
        'dsn' => false,
        'keepAlive' => false,
    ];

    /**
     * @var array<string, mixed>
     */
    #[Override]
    #[SensitivePropertyArray([
        'host',
        'username',
        'password',
        'port',
    ])]
    protected array $config;

    /**
     * @var resource|null
     */
    protected $socket;

    /**
     * Destroys the SmtpMailer.
     */
    public function __destruct()
    {
        if ($this->socket) {
            $this->sendCommand('quit');
        }
    }

    /**
     * Wakes up the SmtpMailer.
     */
    public function __wakeup()
    {
        $this->socket = null;
    }

    /**
     * {@inheritDoc}
     *
     * @throws MailException If the email has no recipients or could not be sent.
     */
    #[Override]
    public function send(Email $email): void
    {
        static::checkEmail($email);

        if (!$this->socket) {
            $this->connect();
        }

        $from = $email->getReturnPath();

        if ($from === []) {
            $from = $email->getFrom();
        }

        $fromAddress = (string) array_key_first($from);
        $this->sendCommand('from', $fromAddress);

        $recipients = $email->getRecipients();

        foreach ($recipients as $recipient => $name) {
            $this->sendCommand('to', $recipient);
        }

        $this->sendCommand('data');

        $headers = $email->getFullHeaderString();
        $body = $email->getFullBodyString();
        $body = (string) preg_replace('/^\./m', '..$1', $body);

        $this->sendData(
            $headers."\r\n\r\n".
            $body."\r\n\r\n"
        );
        $this->sendCommand('dot');

        $this->end();
    }

    /**
     * Authenticates the connection.
     *
     * @throws MailException If the authentication failed.
     */
    protected function authenticate(): void
    {
        $this->sendData('AUTH LOGIN');

        $reply = $this->getData();

        if (str_starts_with($reply, '503')) {
            return;
        }

        if (!str_starts_with($reply, '334')) {
            throw new MailException('SMTP authentication failed.');
        }

        base64_encode($this->config['username']) |> $this->sendData(...);

        $reply = $this->getData();
        if (!str_starts_with($reply, '334')) {
            throw new MailException('SMTP authentication failed.');
        }

        base64_encode($this->config['password']) |> $this->sendData(...);

        $reply = $this->getData();
        if (!str_starts_with($reply, '235')) {
            throw new MailException('SMTP authentication failed.');
        }
    }

    /**
     * Connects to the server.
     *
     * @throws MailException If the connection could not be established.
     */
    protected function connect(): void
    {
        $this->socket = stream_socket_client(
            $this->config['host'].':'.$this->config['port'],
            $errno,
            $errstr,
            10,
            STREAM_CLIENT_CONNECT,
            stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ])
        ) ?: null;

        if (!is_resource($this->socket)) {
            throw new MailException('SMTP connection failed.');
        }

        stream_set_timeout($this->socket, 5);

        $welcome = $this->getData();

        $this->sendCommand('hello');

        if ($this->config['tls']) {
            $this->sendCommand('starttls');

            if (is_resource($this->socket)) {
                stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            }

            $this->sendCommand('hello');
        }

        if ($this->config['auth']) {
            $this->authenticate();
        }
    }

    /**
     * Closes the connection.
     */
    protected function end(): void
    {
        if ($this->config['keepAlive']) {
            $this->sendCommand('reset');
        } else {
            $this->sendCommand('quit');
        }
    }

    /**
     * Reads data from the socket.
     *
     * @return string The data.
     */
    protected function getData(): string
    {
        if (!is_resource($this->socket)) {
            return '';
        }

        $data = '';
        while (($str = fgets($this->socket, 512)) !== false) {
            $data .= $str;
        }

        return $data;
    }

    /**
     * Sends a command.
     *
     * @param string $command The command.
     * @param string $data The data.
     *
     * @throws MailException If the command or response are not valid.
     */
    protected function sendCommand(string $command, string $data = ''): void
    {
        if (!is_resource($this->socket)) {
            return;
        }

        switch ($command) {
            case 'hello':
                if ($this->config['auth']) {
                    $message = 'EHLO';
                } else {
                    $message = 'HELO';
                }

                $message .= ' '.$this->getClient();
                $response = '250';
                break;
            case 'starttls':
                $message = 'STARTTLS';
                $response = '220';
                break;
            case 'from':
                $message = 'MAIL FROM:<'.$data.'>';
                $response = '250';
                break;
            case 'to':
                $message = 'RCPT TO:<'.$data.'>';
                if ($this->config['dsn']) {
                    $message .= ' NOTIFY=SUCCESS,DELAY,FAILURE ORCPT=rfc822;'.$data;
                }
                $response = '250';
                break;
            case 'data':
                $message = 'DATA';
                $response = '354';
                break;
            case 'dot':
                $message = '.';
                $response = '250';
                break;
            case 'reset':
                $message = 'RSET';
                $response = '250';
                break;
            case 'quit':
                $message = 'QUIT';
                $response = '221';
                break;
            default:
                throw new MailException(sprintf(
                    'SMTP command `%s` is not valid.',
                    $command
                ));
        }

        $this->sendData($message);

        $reply = $this->getData();

        if (!str_starts_with($reply, $response)) {
            throw new MailException(sprintf(
                'SMTP invalid reply: %s',
                $reply
            ));
        }

        if (is_resource($this->socket) && $command === 'quit') {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    /**
     * Sends data to the socket.
     *
     * @param string $data The data to send.
     *
     * @throws MailException If the data could not be sent.
     */
    protected function sendData(string $data): void
    {
        if (!is_resource($this->socket)) {
            return;
        }

        $data .= "\r\n";
        $length = strlen($data);
        $written = 0;
        while ($written < $length) {
            if (($result = fwrite($this->socket, substr($data, $written))) === false) {
                throw new MailException('SMTP error sending data.');
            }

            $written += $result;
        }
    }
}
