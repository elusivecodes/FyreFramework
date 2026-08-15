<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FormBuilder;

trait TextareaTestTrait
{
    public function testTextarea(): void
    {
        $this->assertSame(
            '<textarea></textarea>',
            $this->form->textarea()
        );
    }

    public function testTextareaAttributeArray(): void
    {
        $this->assertSame(
            '<textarea data-test="[1,2]"></textarea>',
            $this->form->textarea(attributes: [
                'data-test' => [1, 2],
            ])
        );
    }

    public function testTextareaAttributeEscape(): void
    {
        $this->assertSame(
            '<textarea data-test="&lt;test&gt;"></textarea>',
            $this->form->textarea(attributes: [
                'data-test' => '<test>',
            ])
        );
    }

    public function testTextareaAttributeInvalid(): void
    {
        $this->assertSame(
            '<textarea class="test"></textarea>',
            $this->form->textarea(attributes: [
                '*class*' => 'test',
            ])
        );
    }

    public function testTextareaAttributes(): void
    {
        $this->assertSame(
            '<textarea class="test" id="textarea"></textarea>',
            $this->form->textarea(attributes: [
                'class' => 'test',
                'id' => 'textarea',
            ])
        );
    }

    public function testTextareaAttributesOrder(): void
    {
        $this->assertSame(
            '<textarea class="test" id="textarea"></textarea>',
            $this->form->textarea(attributes: [
                'id' => 'textarea',
                'class' => 'test',
            ])
        );
    }

    public function testTextareaName(): void
    {
        $this->assertSame(
            '<textarea name="textarea"></textarea>',
            $this->form->textarea('textarea')
        );
    }

    public function testTextareaValue(): void
    {
        $this->assertSame(
            '<textarea>Test</textarea>',
            $this->form->textarea(attributes: [
                'value' => 'Test',
            ])
        );
    }

    public function testTextareaValueEscape(): void
    {
        $this->assertSame(
            '<textarea>&lt;test&gt;</textarea>',
            $this->form->textarea(attributes: [
                'value' => '<test>',
            ])
        );
    }
}
