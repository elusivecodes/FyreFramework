<?php
declare(strict_types=1);

namespace Tests\Mock\Core\Container;

class OuterService
{
    public function __construct(
        protected InnerService $innerService
    ) {}

    public function getInnerService(): InnerService
    {
        return $this->innerService;
    }
}
