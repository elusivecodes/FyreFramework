<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait Ipv4TestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function ipv4Provider(): array
    {
        return [
            'value' => [['test' => '1.1.1.1'], []],
            'empty' => [['test' => ''], []],
            'invalid' => [['test' => 'invalid'], ['test' => ['The test must be a valid IPv4 address.']]],
            'missing' => [[], []],
            'v6' => [['test' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334'], ['test' => ['The test must be a valid IPv4 address.']]],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('ipv4Provider')]
    public function testIpv4(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::ipv4());

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
