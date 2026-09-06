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
        ];
    }

    public function testTextarea(): void
    {
        $this->assertSame(
            '<textarea id="textarea-value" name="textarea_value" placeholder="Textarea Value"></textarea>',
            $this->view->Form->textarea('textarea_value')
        );
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

    public function testTextareaDot(): void
    {
        $this->assertSame(
            '<textarea id="key-textarea-value" name="key[textarea_value]" placeholder="Textarea Value"></textarea>',
            $this->view->Form->textarea('key.textarea_value')
        );
    }

    public function testTextareaDotDeep(): void
    {
        $this->assertSame(
            '<textarea id="deep-key-textarea-value" name="deep[key][textarea_value]" placeholder="Textarea Value"></textarea>',
            $this->view->Form->textarea('deep.key.textarea_value')
        );
    }

    public function testTextareaId(): void
    {
        $this->assertSame(
            '<textarea id="other" name="textarea" placeholder="Textarea"></textarea>',
            $this->view->Form->textarea('textarea', [
                'id' => 'other',
            ])
        );
    }

    public function testTextareaIdFalse(): void
    {
        $this->assertSame(
            '<textarea name="textarea" placeholder="Textarea"></textarea>',
            $this->view->Form->textarea('textarea', [
                'id' => false,
            ])
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

    public function testTextareaName(): void
    {
        $this->assertSame(
            '<textarea id="textarea" name="other" placeholder="Textarea"></textarea>',
            $this->view->Form->textarea('textarea', [
                'name' => 'other',
            ])
        );
    }

    public function testTextareaNameFalse(): void
    {
        $this->assertSame(
            '<textarea id="textarea" placeholder="Textarea"></textarea>',
            $this->view->Form->textarea('textarea', [
                'name' => false,
            ])
        );
    }

    public function testTextareaPlaceholder(): void
    {
        $this->assertSame(
            '<textarea id="textarea" name="textarea" placeholder="Other"></textarea>',
            $this->view->Form->textarea('textarea', [
                'placeholder' => 'Other',
            ])
        );
    }

    public function testTextareaPlaceholderFalse(): void
    {
        $this->assertSame(
            '<textarea id="textarea" name="textarea"></textarea>',
            $this->view->Form->textarea('textarea', [
                'placeholder' => false,
            ])
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

    public function testTextareaValuePost(): void
    {
        Closure::bind(function(): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody([
                'textarea' => 'test',
            ]);
        }, $this->view, View::class)();

        $this->assertSame(
            '<textarea id="textarea" name="textarea" placeholder="Textarea">test</textarea>',
            $this->view->Form->textarea('textarea')
        );
    }

    public function testTextareaValuePostDot(): void
    {
        Closure::bind(function(): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody([
                'key' => [
                    'textarea' => 'test',
                ],
            ]);
        }, $this->view, View::class)();

        $this->assertSame(
            '<textarea id="key-textarea" name="key[textarea]" placeholder="Textarea">test</textarea>',
            $this->view->Form->textarea('key.textarea')
        );
    }
}
