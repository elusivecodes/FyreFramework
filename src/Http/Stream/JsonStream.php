<?php
declare(strict_types=1);

namespace Fyre\Http\Stream;

use Generator;
use JsonException;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * Provides a streaming JSON array.
 */
class JsonStream extends IterableStream
{
    /**
     * Constructs a JsonStream.
     *
     * @param iterable<mixed> $items The items to encode.
     *
     * @throws JsonException If an item cannot be encoded.
     */
    public function __construct(iterable $items)
    {
        parent::__construct(static function() use ($items): Generator {
            yield '[';

            $separator = '';

            foreach ($items as $item) {
                yield $separator.(string) json_encode($item, JSON_THROW_ON_ERROR);

                $separator = ',';
            }

            yield ']';
        });
    }
}
