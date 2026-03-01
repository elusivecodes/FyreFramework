<?php
declare(strict_types=1);

namespace Fyre\Http;

use InvalidArgumentException;
use Override;
use Psr\Http\Message\ResponseInterface;

use function sprintf;

/**
 * Provides a base implementation of PSR-7 {@see ResponseInterface} and a status code map
 * used to provide default reason phrases.
 */
class Response extends Message implements ResponseInterface
{
    protected const STATUS_CODES = [
        // 1xx: Informational
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        // 2xx: Success
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        // 3xx: Redirection
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // 4xx: Client error
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Content Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Content',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        499 => 'Client Closed Request',
        // 5xx: Server error
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error',
    ];

    protected string $reasonPhrase;

    protected int $statusCode;

    /**
     * Constructs a Response.
     *
     * If `reasonPhrase` is not provided (or is an empty string), a default phrase is used
     * when available for the status code.
     *
     * @param array<string, mixed> $options The response options.
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $options['statusCode'] ??= 200;
        $options['reasonPhrase'] ??= '';

        $this->statusCode = static::filterStatusCode($options['statusCode']);
        $this->reasonPhrase = $options['reasonPhrase'] ?: (static::STATUS_CODES[$this->statusCode] ?? '');
    }

    /**
     * Returns the response reason phrase.
     *
     * @return string The response reason phrase.
     */
    #[Override]
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * Returns the status code.
     *
     * @return int The status code.
     */
    #[Override]
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Returns the new Response instance with the updated status code.
     *
     * If `$reasonPhrase` is an empty string, a default phrase is used when available for the
     * status code.
     *
     * @param int $code The status code.
     * @param string $reasonPhrase The reason phrase.
     * @return static The new Response instance with the updated status code.
     */
    #[Override]
    public function withStatus(int $code, string $reasonPhrase = ''): static
    {
        $temp = clone $this;

        $temp->statusCode = static::filterStatusCode($code);
        $temp->reasonPhrase = $reasonPhrase ?: (static::STATUS_CODES[$temp->statusCode] ?? '');

        return $temp;
    }

    /**
     * Filters the status code.
     *
     * @param int $code The status code.
     * @return int The filtered status code.
     *
     * @throws InvalidArgumentException If the status code is not valid.
     */
    protected static function filterStatusCode(int $code): int
    {
        if ($code < 100 || $code > 599) {
            throw new InvalidArgumentException(sprintf(
                'Status code `%d` is not valid.',
                $code
            ));
        }

        return $code;
    }
}
