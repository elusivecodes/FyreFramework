<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

use PHPUnit\Framework\Attributes\DataProvider;

trait ButtonTestTrait
{
    /**
     * @return array<string, array{string, array<string, mixed>, string}>
     */
    public static function buttonAttributesProvider(): array
    {
        return [
            'array value' => [
                '',
                ['data-test' => [1, 2]],
                '<button data-test="[1,2]" type="button"></button>',
            ],
            'escaped value' => [
                '',
                ['data-test' => '<test>'],
                '<button data-test="&lt;test&gt;" type="button"></button>',
            ],
            'invalid name' => [
                '',
                ['*class*' => 'test'],
                '<button class="test" type="button"></button>',
            ],
            'multiple attributes' => [
                'Test',
                ['class' => 'test', 'id' => 'button'],
                '<button class="test" id="button" type="button">Test</button>',
            ],
            'attribute order' => [
                '',
                ['id' => 'button', 'class' => 'test'],
                '<button class="test" id="button" type="button"></button>',
            ],
        ];
    }

    public function testButton(): void
    {
        $this->assertSame(
            '<button type="button"></button>',
            $this->view->Form->button()
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('buttonAttributesProvider')]
    public function testButtonAttributes(string $content, array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->button($content, $attributes)
        );
    }

    public function testButtonContent(): void
    {
        $this->assertSame(
            '<button type="button">Test</button>',
            $this->view->Form->button('Test')
        );
    }

    public function testButtonContentEscape(): void
    {
        $this->assertSame(
            '<button type="button">&lt;i&gt;Test&lt;/i&gt;</button>',
            $this->view->Form->button('<i>Test</i>')
        );
    }

    public function testButtonContentNoEscape(): void
    {
        $this->assertSame(
            '<button type="button"><i>Test</i></button>',
            $this->view->Form->button('<i>Test</i>', escape: false)
        );
    }
}
