<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

trait RequestMethodTrait
{
    public function testDeleteRequest(): void
    {
        $this->enableCsrfToken();
        $this->delete('/method');

        $this->assertHeader('DELETE', 'Request-Method');
    }

    public function testHeadRequest(): void
    {
        $this->head('/method');

        $this->assertHeader('HEAD', 'Request-Method');
    }

    public function testOptionsRequest(): void
    {
        $this->options('/method');

        $this->assertHeader('OPTIONS', 'Request-Method');
    }

    public function testPatchRequest(): void
    {
        $this->enableCsrfToken();
        $this->patch('/method');

        $this->assertHeader('PATCH', 'Request-Method');
    }
}
