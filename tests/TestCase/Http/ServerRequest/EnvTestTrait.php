<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ServerRequest;

use Fyre\Http\ServerRequest;
use Fyre\Utility\DateTime\DateTime;

use function putenv;

trait EnvTestTrait
{
    public function testGetEnv(): void
    {
        putenv('test=value');

        $request = new ServerRequest($this->config, $this->type);

        $this->assertSame(
            'value',
            $request->getEnv('test')
        );
    }

    public function testGetEnvInvalid(): void
    {
        $request = new ServerRequest($this->config, $this->type);

        $this->assertNull(
            $request->getEnv('invalid')
        );
    }

    public function testGetEnvType(): void
    {
        putenv('value=2024-12-31');

        $request = new ServerRequest($this->config, $this->type);

        $value = $request->getEnv('value', 'date');

        $this->assertInstanceOf(
            DateTime::class,
            $value
        );

        $this->assertSame(
            '2024-12-31T00:00:00.000+00:00',
            $value->toISOString()
        );
    }
}
