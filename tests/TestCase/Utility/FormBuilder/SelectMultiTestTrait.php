<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FormBuilder;

trait SelectMultiTestTrait
{
    public function testSelectMulti(): void
    {
        $this->assertSame(
            '<select multiple></select>',
            $this->form->selectMulti()
        );
    }

    public function testSelectMultiAttributeArray(): void
    {
        $this->assertSame(
            '<select data-test="[1,2]" multiple></select>',
            $this->form->selectMulti(attributes: [
                'data-test' => [1, 2],
            ])
        );
    }

    public function testSelectMultiAttributeEscape(): void
    {
        $this->assertSame(
            '<select data-test="&lt;test&gt;" multiple></select>',
            $this->form->selectMulti(attributes: [
                'data-test' => '<test>',
            ])
        );
    }

    public function testSelectMultiAttributeInvalid(): void
    {
        $this->assertSame(
            '<select class="test" multiple></select>',
            $this->form->selectMulti(attributes: [
                '*class*' => 'test',
            ])
        );
    }

    public function testSelectMultiAttributes(): void
    {
        $this->assertSame(
            '<select class="test" id="select" multiple></select>',
            $this->form->selectMulti(attributes: [
                'class' => 'test',
                'id' => 'select',
            ])
        );
    }

    public function testSelectMultiAttributesOrder(): void
    {
        $this->assertSame(
            '<select class="test" id="select" multiple></select>',
            $this->form->selectMulti(attributes: [
                'id' => 'select',
                'class' => 'test',
            ])
        );
    }

    public function testSelectMultiName(): void
    {
        $this->assertSame(
            '<select name="select" multiple></select>',
            $this->form->selectMulti('select')
        );
    }

    public function testSelectMultiOptionGroup(): void
    {
        $this->assertSame(
            '<select multiple><optgroup label="test"><option value="0">A</option><option value="1">B</option></optgroup></select>',
            $this->form->selectMulti(options: [
                [
                    'label' => 'test',
                    'children' => [
                        'A',
                        'B',
                    ],
                ],
            ])
        );
    }

    public function testSelectMultiOptions(): void
    {
        $this->assertSame(
            '<select multiple><option value="0">A</option><option value="1">B</option></select>',
            $this->form->selectMulti(options: [
                'A',
                'B',
            ])
        );
    }

    public function testSelectMultiOptionsAssoc(): void
    {
        $this->assertSame(
            '<select multiple><option value="a">A</option></select>',
            $this->form->selectMulti(options: [
                'a' => 'A',
            ])
        );
    }

    public function testSelectMultiOptionsAttributes(): void
    {
        $this->assertSame(
            '<select multiple><option value="a">A</option></select>',
            $this->form->selectMulti(options: [
                [
                    'value' => 'a',
                    'label' => 'A',
                ],
            ])
        );
    }

    public function testSelectMultiOptionsAttributesEscape(): void
    {
        $this->assertSame(
            '<select multiple><option data-test="&lt;test&gt;" value="a">A</option></select>',
            $this->form->selectMulti(options: [
                [
                    'value' => 'a',
                    'label' => 'A',
                    'data-test' => '<test>',
                ],
            ])
        );
    }

    public function testSelectMultiOptionsAttributesInvalid(): void
    {
        $this->assertSame(
            '<select multiple><option class="test" value="a">A</option></select>',
            $this->form->selectMulti(options: [
                [
                    'value' => 'a',
                    'label' => 'A',
                    '*class*' => 'test',
                ],
            ])
        );
    }

    public function testSelectMultiOptionsEscape(): void
    {
        $this->assertSame(
            '<select multiple><option value="0">&lt;test&gt;</option></select>',
            $this->form->selectMulti(options: [
                '<test>',
            ])
        );
    }

    public function testSelectMultiSelected(): void
    {
        $this->assertSame(
            '<select multiple><option value="0">A</option><option value="1" selected>B</option></select>',
            $this->form->selectMulti(attributes: [
                'value' => 1,
            ], options: [
                'A',
                'B',
            ])
        );
    }

    public function testSelectMultiSelectedArray(): void
    {
        $this->assertSame(
            '<select multiple><option value="0">A</option><option value="1" selected>B</option><option value="2" selected>C</option></select>',
            $this->form->selectMulti(attributes: [
                'value' => [1, 2],
            ], options: [
                'A',
                'B',
                'C',
            ])
        );
    }
}
