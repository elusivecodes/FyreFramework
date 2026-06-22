<?php
declare(strict_types=1);

namespace Tests\Mock\Core\Container;

class ArgumentService
{
    /**
     * @var int[]
     */
    protected array $arguments;

    public function __construct(int $a = 1, int $b = 2, int $c = 3)
    {
        $this->arguments = [$a, $b, $c];
    }

    /**
     * @return int[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
