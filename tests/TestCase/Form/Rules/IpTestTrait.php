<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait IpTestTrait
{
    public function testIp(): void
    {
        $this->validator->add('test', Rule::ip());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '1.1.1.1',
            ])
        );
    }

    public function testIpEmpty(): void
    {
        $this->validator->add('test', Rule::ip());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testIpInvalid(): void
    {
        $this->validator->add('test', Rule::ip());

        $this->assertSame(
            [
                'test' => ['The test must be a valid IP address.'],
            ],
            $this->validator->validate([
                'test' => 'invalid',
            ])
        );
    }

    public function testIpMissing(): void
    {
        $this->validator->add('test', Rule::ip());

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }

    public function testIpWithV6(): void
    {
        $this->validator->add('test', Rule::ip());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            ])
        );
    }
}
