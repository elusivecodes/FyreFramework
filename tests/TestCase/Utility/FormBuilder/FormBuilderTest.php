<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FormBuilder;

use Fyre\Core\Config;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Utility\FormBuilder;
use Fyre\Utility\HtmlHelper;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class FormBuilderTest extends TestCase
{
    use ButtonTestTrait;
    use CloseTestTrait;
    use FieldsetCloseTestTrait;
    use FieldsetOpenTestTrait;
    use InputTestTrait;
    use InputTypeTestTrait;
    use LabelTestTrait;
    use LegendTestTrait;
    use OpenMultipartTestTrait;
    use OpenTestTrait;
    use SelectMultiTestTrait;
    use SelectTestTrait;
    use TextareaTestTrait;

    protected FormBuilder $form;

    protected HtmlHelper $html;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(FormBuilder::class)
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(FormBuilder::class)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $config = new Config();
        $config->set('App.charset', 'UTF-8');

        $this->html = new HtmlHelper($config);
        $this->form = new FormBuilder($this->html);
    }
}
