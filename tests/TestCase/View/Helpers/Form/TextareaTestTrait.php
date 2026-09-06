<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

use Closure;
use Fyre\View\View;
use PHPUnit\Framework\Attributes\DataProvider;

trait TextareaTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function textareaAttributesProvider(): array
    {
        return [
            'array value' => [
                ['data-test' => [1, 2]],
                '<textarea id="textarea" name="textarea" data-test="[1,2]" placeholder="Textarea"></textarea>',
            ],
            'escaped value' => [
                ['data-test' => '<test>'],
                '<textarea id="textarea" name="textarea" data-test="&lt;test&gt;" placeholder="Textarea"></textarea>',
            ],
            'invalid name' => [
                ['*class*' => 'test'],
                '<textarea class="test" id="textarea" name="textarea" placeholder="Textarea"></textarea>',
            ],
            'multiple attributes' => [
                ['class' => 'test', 'id' => 'other'],
                '<textarea class="test" id="other" name="textarea" placeholder="Textarea"></textarea>',
            ],
            'attribute order' => [
                ['id' => 'other', 'class' => 'test'],
                '<textarea class="test" id="other" name="textarea" placeholder="Textarea"></textarea>',
            ],
            'id' => [
                ['id' => 'other'],
                '<textarea id="other" name="textarea" placeholder="Textarea"></textarea>',
            ],
            'id false' => [
                ['id' => false],
                '<textarea name="textarea" placeholder="Textarea"></textarea>',
            ],
            'name' => [
                ['name' => 'other'],
                '<textarea id="textarea" name="other" placeholder="Textarea"></textarea>',
            ],
            'name false' => [
                ['name' => false],
                '<textarea id="textarea" placeholder="Textarea"></textarea>',
            ],
            'placeholder' => [
                ['placeholder' => 'Other'],
                '<textarea id="textarea" name="textarea" placeholder="Other"></textarea>',
            ],
            'placeholder false' => [
                ['placeholder' => false],
                '<textarea id="textarea" name="textarea"></textarea>',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function textareaFieldNameProvider(): array
    {
        return [
            'flat' => [
                'textarea_value',
                '<textarea id="textarea-value" name="textarea_value" placeholder="Textarea Value"></textarea>',
            ],
            'dotted' => [
                'key.textarea_value',
                '<textarea id="key-textarea-value" name="key[textarea_value]" placeholder="Textarea Value"></textarea>',
            ],
            'deeply dotted' => [
                'deep.key.textarea_value',
                '<textarea id="deep-key-textarea-value" name="deep[key][textarea_value]" placeholder="Textarea Value"></textarea>',
            ],
        ];
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function textareaValuePostProvider(): array
    {
        return [
            'flat' => [
                [
                    'textarea' => 'test',
                ],
                'textarea',
                '<textarea id="textarea" name="textarea" placeholder="Textarea">test</textarea>',
            ],
            'dotted' => [
                [
                    'key' => [
                        'textarea' => 'test',
                    ],
                ],
                'key.textarea',
                '<textarea id="key-textarea" name="key[textarea]" placeholder="Textarea">test</textarea>',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('textareaAttributesProvider')]
    public function testTextareaAttributes(array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->textarea('textarea', $attributes)
        );
    }

    #[DataProvider('textareaFieldNameProvider')]
    public function testTextareaFieldName(string $field, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->textarea($field)
        );
    }

    public function testTextareaIdPrefix(): void
    {
        $this->view->Form->open(idPrefix: 'test');

        $this->assertSame(
            '<textarea id="test-textarea" name="textarea" placeholder="Textarea"></textarea>',
            $this->view->Form->textarea('textarea')
        );
    }

    public function testTextareaValue(): void
    {
        $this->assertSame(
            '<textarea id="textarea" name="textarea" placeholder="Textarea">Test</textarea>',
            $this->view->Form->textarea('textarea', [
                'value' => 'Test',
            ])
        );
    }

    public function testTextareaValueDefault(): void
    {
        $this->assertSame(
            '<textarea id="textarea" name="textarea" placeholder="Textarea">test</textarea>',
            $this->view->Form->textarea('textarea', [
                'default' => 'test',
            ])
        );
    }

    public function testTextareaValueEscape(): void
    {
        $this->assertSame(
            '<textarea id="textarea" name="textarea" placeholder="Textarea">&lt;test&gt;</textarea>',
            $this->view->Form->textarea('textarea', [
                'value' => '<test>',
            ])
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('textareaValuePostProvider')]
    public function testTextareaValuePost(array $data, string $field, string $expected): void
    {
        Closure::bind(function() use ($data): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody($data);
        }, $this->view, View::class)();

        $this->assertSame(
            $expected,
            $this->view->Form->textarea($field)
        );
    }
}
