<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility;

use Fyre\Core\Config;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Utility\HtmlHelper;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class HtmlHelperTest extends TestCase
{
    protected HtmlHelper $html;

    public function testAttributes(): void
    {
        $this->assertSame(
            ' href="#"',
            $this->html->attributes([
                'href' => '#',
            ])
        );
    }

    public function testAttributesArray(): void
    {
        $this->assertSame(
            ' data-test="[1,2,3]"',
            $this->html->attributes([
                'data-test' => [1, 2, 3],
            ])
        );
    }

    public function testAttributesEmpty(): void
    {
        $this->assertSame(
            '',
            $this->html->attributes([])
        );
    }

    public function testAttributesEscape(): void
    {
        $this->assertSame(
            ' data-test="&quot;value&quot;"',
            $this->html->attributes([
                'data-test' => '"value"',
            ])
        );
    }

    public function testAttributesFalse(): void
    {
        $this->assertSame(
            ' disabled="false"',
            $this->html->attributes([
                'disabled' => false,
            ])
        );
    }

    public function testAttributesNumericKey(): void
    {
        $this->assertSame(
            ' disabled',
            $this->html->attributes([
                'disabled',
            ])
        );
    }

    public function testAttributesOrder(): void
    {
        $this->assertSame(
            ' class="test" href="#"',
            $this->html->attributes([
                'href' => '#',
                'class' => 'test',
            ])
        );
    }

    public function testAttributesTrue(): void
    {
        $this->assertSame(
            ' disabled',
            $this->html->attributes([
                'disabled' => true,
            ])
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
