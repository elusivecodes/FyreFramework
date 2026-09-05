<?php
declare(strict_types=1);

namespace Fyre\Queue;

use Throwable;
use UnexpectedValueException;

use function array_diff_key;

/**
 * Represents a terminal queue failure.
 *
 * @internal
 */
final class FailedMessage
{
    /**
     * @var class-string<Throwable>|null
     */
    protected string|null $exceptionClass = null;

    protected int|string|null $exceptionCode = null;

    protected string|null $exceptionFile = null;

    protected int|null $exceptionLine = null;

    protected string|null $exceptionMessage = null;

    protected string|null $exceptionTrace = null;

    protected int $failedAt;

    /**
     * @var array<string, mixed>
     */
    protected array $message;

    /**
     * Constructs a FailedMessage.
     *
     * @param Message $message The Message.
     * @param int $failedAt The failure timestamp.
     * @param Throwable|null $exception The exception that caused the failure.
     */
    public function __construct(Message $message, int $failedAt, Throwable|null $exception = null)
    {
        $this->message = $message->getConfig();
        $this->failedAt = $failedAt;

        if ($exception === null) {
            return;
        }

        $this->exceptionClass = $exception::class;
        $this->exceptionMessage = $exception->getMessage();
        $this->exceptionCode = $exception->getCode();
        $this->exceptionFile = $exception->getFile();
        $this->exceptionLine = $exception->getLine();
        $this->exceptionTrace = $exception->getTraceAsString();
    }

    /**
     * Serializes the failed message.
     *
     * @return array<string, mixed> The serialized data.
     */
    public function __serialize(): array
    {
        return [
            'message' => $this->message,
            'failedAt' => $this->failedAt,
            'exceptionClass' => $this->exceptionClass,
            'exceptionMessage' => $this->exceptionMessage,
            'exceptionCode' => $this->exceptionCode,
            'exceptionFile' => $this->exceptionFile,
            'exceptionLine' => $this->exceptionLine,
            'exceptionTrace' => $this->exceptionTrace,
        ];
    }

    /**
     * Unserializes the failed message.
     *
     * @param array<string, mixed> $data The serialized data.
     */
    public function __unserialize(array $data): void
    {
        if (array_diff_key([
            'message' => true,
            'failedAt' => true,
            'exceptionClass' => true,
            'exceptionMessage' => true,
            'exceptionCode' => true,
            'exceptionFile' => true,
            'exceptionLine' => true,
            'exceptionTrace' => true,
        ], $data) !== []) {
            throw new UnexpectedValueException('Failed message data is not valid.');
        }

        $this->message = $data['message'];
        $this->failedAt = $data['failedAt'];
        $this->exceptionClass = $data['exceptionClass'];
        $this->exceptionMessage = $data['exceptionMessage'];
        $this->exceptionCode = $data['exceptionCode'];
        $this->exceptionFile = $data['exceptionFile'];
        $this->exceptionLine = $data['exceptionLine'];
        $this->exceptionTrace = $data['exceptionTrace'];
    }

    /**
     * Returns the exception class.
     *
     * @return class-string<Throwable>|null The exception class.
     */
    public function getExceptionClass(): string|null
    {
        return $this->exceptionClass;
    }

    /**
     * Returns the exception code.
     *
     * @return int|string|null The exception code.
     */
    public function getExceptionCode(): int|string|null
    {
        return $this->exceptionCode;
    }

    /**
     * Returns the exception file.
     *
     * @return string|null The exception file.
     */
    public function getExceptionFile(): string|null
    {
        return $this->exceptionFile;
    }

    /**
     * Returns the exception line.
     *
     * @return int|null The exception line.
     */
    public function getExceptionLine(): int|null
    {
        return $this->exceptionLine;
    }

    /**
     * Returns the exception message.
     *
     * @return string|null The exception message.
     */
    public function getExceptionMessage(): string|null
    {
        return $this->exceptionMessage;
    }

    /**
     * Returns the exception trace.
     *
     * @return string|null The exception trace.
     */
    public function getExceptionTrace(): string|null
    {
        return $this->exceptionTrace;
    }

    /**
     * Returns the failure timestamp.
     *
     * @return int The failure timestamp.
     */
    public function getFailedAt(): int
    {
        return $this->failedAt;
    }

    /**
     * Returns a fresh Message.
     *
     * @return Message The Message.
     */
    public function getMessage(): Message
    {
        return new Message($this->message);
    }
}
