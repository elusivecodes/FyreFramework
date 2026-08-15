<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

use Fyre\Utility\HtmlHelper;

trait OpenMultipartTestTrait
{
    public function testOpenMultipart(): void
    {
        $this->assertSame(
            '<form action="/test" method="post" enctype="multipart/form-data" accept-charset="UTF-8">',
            $this->view->Form->openMultipart()
        );
    }

    public function testOpenMultipartAction(): void
    {
        $this->assertSame(
            '<form action="/test/test-method" method="post" enctype="multipart/form-data" accept-charset="UTF-8">',
            $this->view->Form->openMultipart(attributes: [
                'action' => '/test/test-method',
            ])
        );
    }

    public function testOpenMultipartAttributeArray(): void
    {
        $this->assertSame(
            '<form data-test="[1,2]" action="/test" method="post" enctype="multipart/form-data" accept-charset="UTF-8">',
            $this->view->Form->openMultipart(attributes: [
                'data-test' => [1, 2],
            ])
        );
    }

    public function testOpenMultipartAttributeEscape(): void
    {
        $this->assertSame(
            '<form data-test="&lt;test&gt;" action="/test" method="post" enctype="multipart/form-data" accept-charset="UTF-8">',
            $this->view->Form->openMultipart(attributes: [
                'data-test' => '<test>',
            ])
        );
    }

    public function testOpenMultipartAttributeInvalid(): void
    {
        $this->assertSame(
            '<form class="test" action="/test" method="post" enctype="multipart/form-data" accept-charset="UTF-8">',
            $this->view->Form->openMultipart(attributes: [
                '*class*' => 'test',
            ])
        );
    }

    public function testOpenMultipartAttributes(): void
    {
        $this->assertSame(
            '<form class="test" id="form" action="/test" method="post" enctype="multipart/form-data" accept-charset="UTF-8">',
            $this->view->Form->openMultipart(attributes: [
                'class' => 'test',
                'id' => 'form',
            ])
        );
    }

    public function testOpenMultipartAttributesOrder(): void
    {
        $this->assertSame(
            '<form class="test" id="form" action="/test" method="post" enctype="multipart/form-data" accept-charset="UTF-8">',
            $this->view->Form->openMultipart(attributes: [
                'id' => 'form',
                'class' => 'test',
            ])
        );
    }

    public function testOpenMultipartCharset(): void
    {
        $this->container->use(HtmlHelper::class)->setCharset('ISO-8859-1');

        $this->assertSame(
            '<form action="/test" method="post" enctype="multipart/form-data" accept-charset="ISO-8859-1">',
            $this->view->Form->openMultipart()
        );
    }
}
