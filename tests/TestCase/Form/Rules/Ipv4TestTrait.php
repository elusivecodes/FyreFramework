<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait Ipv4TestTrait
{
    public function testIpv4(): void
    {
        $this->validator->add('test', Rule::ipv4());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '1.1.1.1',
            ])
        );
    }

    public function testIpv4Empty(): void
    {
        $this->validator->add('test', Rule::ipv4());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testIpv4Invalid(): void
    {
        $this->validator->add('test', Rule::ipv4());

        $this->assertSame(
            [
                'test' => ['The test must be a valid IPv4 address.'],
            ],
            $this->validator->validate([
                'test' => 'invalid',
            ])
        );
    }

    public function testIpv4Missing(): void
    {
        $this->validator->add('test', Rule::ipv4());

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }

    public function testIpv4WithV6(): void
    {
        $this->validator->add('test', Rule::ipv4());

        $this->assertSame(
            [
                'test' => ['The test must be a valid IPv4 address.'],
            ],
            $this->validator->validate([
                'test' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            ])
        );
    }
}
