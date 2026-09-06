<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait Ipv6TestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function ipv6Provider(): array
    {
        return [
            'value' => [['test' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334'], []],
            'empty' => [['test' => ''], []],
            'invalid' => [['test' => 'invalid'], ['test' => ['The test must be a valid IPv6 address.']]],
            'missing' => [[], []],
            'v4' => [['test' => '1.1.1.1'], ['test' => ['The test must be a valid IPv6 address.']]],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('ipv6Provider')]
    public function testIpv6(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::ipv6());

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
