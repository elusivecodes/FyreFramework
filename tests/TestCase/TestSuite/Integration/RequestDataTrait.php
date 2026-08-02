<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

trait RequestDataTrait
{
    public function testJsonRequestDataPreservesScalarTypes(): void
    {
        $this->enableCsrfToken();
        $this->requestAsJson();

        $this->post('/data', [
            'boolean' => false,
            'integer' => 1,
            'null' => null,
            'nested' => [
                'value' => 2,
            ],
        ]);

        $this->assertResponseEquals('{"boolean":false,"integer":1,"null":null,"nested":{"value":2}}');
    }

    public function testOtherRequestDataUsesStringValues(): void
    {
        $this->enableCsrfToken();
        $this->request['headers']['Content-Type'] = 'text/plain';

        $this->post('/data', [
            'boolean' => false,
            'integer' => 1,
            'null' => null,
            'nested' => [
                'value' => 2,
            ],
        ]);

        $this->assertResponseEquals('{"boolean":"","integer":"1","null":"","nested":{"value":"2"}}');
    }

    public function testRequestData(): void
    {
        $this->enableCsrfToken();
        $this->data([
            'shared' => 'staged',
            'staged' => 1,
        ]);

        $this->post('/data', [
            'direct' => 2,
            'shared' => 'direct',
        ]);

        $this->assertResponseEquals('{"direct":"2","shared":"staged","staged":"1"}');

        $this->post('/data', ['direct' => 3]);

        $this->assertResponseEquals('{"direct":"3"}');
    }

    public function testUrlEncodedRequestDataUsesPhpValues(): void
    {
        $this->enableCsrfToken();

        $data = [
            'boolean' => false,
            'integer' => 1,
            'null' => null,
            'nested' => [
                'value' => 2,
            ],
        ];

        $this->post('/data', $data);

        $this->assertResponseEquals('{"boolean":"0","integer":"1","nested":{"value":"2"}}');

        $this->put('/data', $data);

        $this->assertResponseEquals('{"boolean":"0","integer":"1","nested":{"value":"2"}}');
    }
}
