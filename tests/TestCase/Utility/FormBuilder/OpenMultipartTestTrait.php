<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FormBuilder;

use PHPUnit\Framework\Attributes\DataProvider;

trait OpenMultipartTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function openMultipartAttributesProvider(): array
    {
        return [
            'array value' => [
                ['data-test' => [1, 2]],
                '<form data-test="[1,2]" method="post" enctype="multipart/form-data" accept-charset="UTF-8">',
            ],
            'escaped value' => [
                ['data-test' => '<test>'],
                '<form data-test="&lt;test&gt;" method="post" enctype="multipart/form-data" accept-charset="UTF-8">',
            ],
            'invalid name' => [
                ['*class*' => 'test'],
                '<form class="test" method="post" enctype="multipart/form-data" accept-charset="UTF-8">',
            ],
            'multiple attributes' => [
                ['class' => 'test', 'id' => 'form'],
                '<form class="test" id="form" method="post" enctype="multipart/form-data" accept-charset="UTF-8">',
            ],
            'attribute order' => [
                ['id' => 'form', 'class' => 'test'],
                '<form class="test" id="form" method="post" enctype="multipart/form-data" accept-charset="UTF-8">',
            ],
        ];
    }

    public function testOpenMultipart(): void
    {
        $this->assertSame(
            '<form method="post" enctype="multipart/form-data" accept-charset="UTF-8">',
            $this->form->openMultipart()
        );
    }

    public function testOpenMultipartAction(): void
    {
        $this->assertSame(
            '<form action="/test" method="post" enctype="multipart/form-data" accept-charset="UTF-8">',
            $this->form->openMultipart('/test')
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('openMultipartAttributesProvider')]
    public function testOpenMultipartAttributes(array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->form->openMultipart(attributes: $attributes)
        );
    }

    public function testOpenMultipartCharset(): void
    {
        $this->html->setCharset('ISO-8859-1');

        $this->assertSame(
            '<form method="post" enctype="multipart/form-data" accept-charset="ISO-8859-1">',
            $this->form->openMultipart()
        );
    }
}
