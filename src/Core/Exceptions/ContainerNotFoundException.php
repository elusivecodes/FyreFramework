<?php
declare(strict_types=1);

namespace Fyre\Core\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Represents a container not-found error.
 */
class ContainerNotFoundException extends ContainerException implements NotFoundExceptionInterface {}
