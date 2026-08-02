<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

trait CsrfTrait
{
    public function testCsrfTokenInRequestData(): void
    {
        $this->enableCsrfToken();
        $this->request['headers']['Csrf-Token'] = 'invalid';

        $this->post('/csrf', ['value' => 'form']);

        $this->assertResponseEquals('form');
    }

    public function testCsrfTokenInRequestJsonData(): void
    {
        $this->enableCsrfToken();
        $this->requestAsJson();
        $this->request['headers']['Csrf-Token'] = 'invalid';

        $this->post('/csrf', ['value' => 'json']);

        $this->assertResponseEquals('json');
    }
}
