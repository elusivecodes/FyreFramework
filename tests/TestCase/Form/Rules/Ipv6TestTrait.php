<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait Ipv6TestTrait
{
    public function testIpv6(): void
    {
        $this->validator->add('test', Rule::ipv6());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            ])
        );
    }

    public function testIpv6Empty(): void
    {
        $this->validator->add('test', Rule::ipv6());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testIpv6Invalid(): void
    {
        $this->validator->add('test', Rule::ipv6());

        $this->assertSame(
            [
                'test' => ['The test must be a valid IPv6 address.'],
            ],
            $this->validator->validate([
                'test' => 'invalid',
            ])
        );
    }

    public function testIpv6Missing(): void
    {
        $this->validator->add('test', Rule::ipv6());

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }

    public function testIpv6WithV4(): void
    {
        $this->validator->add('test', Rule::ipv6());

        $this->assertSame(
            [
                'test' => ['The test must be a valid IPv6 address.'],
            ],
            $this->validator->validate([
                'test' => '1.1.1.1',
            ])
        );
    }
}
