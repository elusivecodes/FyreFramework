<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility;

use Fyre\Core\Config;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Utility\HtmlHelper;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class HtmlHelperTest extends TestCase
{
    protected HtmlHelper $html;

    /**
     * @return array<string, array{array<array-key, mixed>, string}>
     */
    public static function attributesProvider(): array
    {
        return [
            'string value' => [
                ['href' => '#'],
                ' href="#"',
            ],
            'array value' => [
                ['data-test' => [1, 2, 3]],
                ' data-test="[1,2,3]"',
            ],
            'empty attributes' => [
                [],
                '',
            ],
            'escaped value' => [
                ['data-test' => '"value"'],
                ' data-test="&quot;value&quot;"',
            ],
            'false value' => [
                ['disabled' => false],
                ' disabled="false"',
            ],
            'numeric key' => [
                ['disabled'],
                ' disabled',
            ],
            'attribute order' => [
                ['href' => '#', 'class' => 'test'],
                ' class="test" href="#"',
            ],
            'true value' => [
                ['disabled' => true],
                ' disabled',
            ],
        ];
    }

    /**
     * @param array<array-key, mixed> $attributes
     */
    #[DataProvider('attributesProvider')]
    public function testAttributes(array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->html->attributes($attributes)
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(HtmlHelper::class)
        );
    }

    public function testEscape(): void
    {
        $this->assertSame(
            '&quot;',
            $this->html->escape('"')
        );
    }

    public function testGetCharset(): void
    {
        $this->assertSame(
            'UTF-8',
            $this->html->getCharset()
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(HtmlHelper::class)
        );
    }

    public function testSetCharset(): void
    {
        $this->html->setCharset('ISO-8859-1');

        $this->assertSame(
            'ISO-8859-1',
            $this->html->getCharset()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $config = new Config();
        $config->set('App.charset', 'UTF-8');

        $this->html = new HtmlHelper($config);
    }
}
