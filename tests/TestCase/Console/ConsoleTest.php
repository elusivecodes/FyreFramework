<?php
declare(strict_types=1);

namespace Tests\TestCase\Console;

use Fyre\Console\Console;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Core\Traits\StaticMacroTrait;
use Override;
use PHPUnit\Framework\TestCase;

use function array_diff;
use function class_uses;
use function exec;
use function fclose;
use function fopen;
use function fwrite;
use function rewind;
use function stream_get_contents;

use const PHP_EOL;

final class ConsoleTest extends TestCase
{
    protected Console $console;

    /**
     * @var resource
     */
    protected $input;

    /**
     * @var resource
     */
    protected $output;

    public function testChoice(): void
    {
        fwrite($this->input, 'a'.PHP_EOL);
        rewind($this->input);

        $this->assertSame(
            'a',
            $this->console->choice('Select one', ['a', 'b', 'c'])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;33mSelect one\033[0m".PHP_EOL.
            " (\033[2;36ma\033[0m/\033[2;36mb\033[0m/\033[2;36mc\033[0m)".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testChoiceAssoc(): void
    {
        fwrite($this->input, 'b'.PHP_EOL);
        rewind($this->input);

        $this->assertSame(
            'b',
            $this->console->choice('Select one', ['a' => 'Test 1', 'b' => 'Test 2', 'c' => 'Test 3'], 'a')
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;33mSelect one\033[0m".PHP_EOL.
            "\033[0;36m  [a]  \033[0m\033[2;37mTest 1\033[0m".PHP_EOL.
            "\033[0;36m  [b]  \033[0m\033[2;37mTest 2\033[0m".PHP_EOL.
            "\033[0;36m  [c]  \033[0m\033[2;37mTest 3\033[0m".PHP_EOL.
            "\033[0;33mChoice\033[0m (\033[1;36ma\033[0m/\033[2;36mb\033[0m/\033[2;36mc\033[0m)".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testChoiceDefault(): void
    {
        fwrite($this->input, 'x'.PHP_EOL);
        rewind($this->input);

        $this->assertSame(
            'a',
            $this->console->choice('Select one', ['a', 'b', 'c'], 'a')
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;33mSelect one\033[0m".PHP_EOL.
            " (\033[1;36ma\033[0m/\033[2;36mb\033[0m/\033[2;36mc\033[0m)".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testComment(): void
    {
        $this->console->comment('Test');

        rewind($this->output);

        $this->assertSame(
            "\033[2;37mTest\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testConfirm(): void
    {
        fwrite($this->input, 'n'.PHP_EOL);
        rewind($this->input);

        $this->assertFalse($this->console->confirm('OK?'));

        rewind($this->output);

        $this->assertSame(
            "\033[0;33mOK?\033[0m".PHP_EOL.
            " (\033[1;36my\033[0m/\033[2;36mn\033[0m)".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testConfirmDefault(): void
    {
        fwrite($this->input, 'x'.PHP_EOL);
        rewind($this->input);

        $this->assertTrue($this->console->confirm('OK?'));

        rewind($this->output);

        $this->assertSame(
            "\033[0;33mOK?\033[0m".PHP_EOL.
            " (\033[1;36my\033[0m/\033[2;36mn\033[0m)".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Console::class)
        );
    }

    public function testError(): void
    {
        $this->console->error('Test');

        rewind($this->output);

        $this->assertSame(
            "\033[0;31mTest\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testGetHeight(): void
    {
        $this->assertSame(
            (int) exec('tput lines'),
            Console::getHeight()
        );
    }

    public function testGetWidth(): void
    {
        $this->assertSame(
            (int) exec('tput cols'),
            Console::getWidth()
        );
    }

    public function testInfo(): void
    {
        $this->console->info('Test');

        rewind($this->output);

        $this->assertSame(
            "\033[0;34mTest\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testInput(): void
    {
        fwrite($this->input, 'This is some test input'.PHP_EOL);
        rewind($this->input);

        $this->assertSame(
            'This is some test input',
            $this->console->input()
        );
    }

    public function testMacro(): void
    {
        $this->assertEmpty(
            array_diff([MacroTrait::class, StaticMacroTrait::class], class_uses(Console::class))
        );
    }

    public function testProgress(): void
    {
        $this->console->progress(5);

        rewind($this->output);

        $this->assertSame(
            "[\033[0;32m#####.....\033[0m] 50%".PHP_EOL,
            stream_get_contents($this->output)
        );

        $this->console->progress();
    }

    public function testProgressClear(): void
    {
        $this->console->progress(5);
        $this->console->progress();

        rewind($this->output);

        $this->assertSame(
            "[\033[0;32m#####.....\033[0m] 50%".PHP_EOL."\033[1A\033[K\007",
            stream_get_contents($this->output)
        );
    }

    public function testProgressTotalSteps(): void
    {
        $this->console->progress(25, 100);

        rewind($this->output);

        $this->assertSame(
            "[\033[0;32m###.......\033[0m] 25%".PHP_EOL,
            stream_get_contents($this->output)
        );

        $this->console->progress();
    }

    public function testPrompt(): void
    {
        fwrite($this->input, 'This is some test input'.PHP_EOL);
        rewind($this->input);

        $this->assertSame(
            'This is some test input',
            $this->console->prompt('This is a prompt')
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;33mThis is a prompt\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testStyle(): void
    {
        $this->assertSame(
            'Test',
            Console::style('Test')
        );
    }

    public function testStyleBackground(): void
    {
        $this->assertSame(
            "\033[0;37;44mTest\033[0m",
            Console::style('Test', background: Console::BLUE)
        );
    }

    public function testStyleBold(): void
    {
        $this->assertSame(
            "\033[1;37mTest\033[0m",
            Console::style('Test', style: Console::BOLD)
        );
    }

    public function testStyleColor(): void
    {
        $this->assertSame(
            "\033[0;34mTest\033[0m",
            Console::style('Test', Console::BLUE)
        );
    }

    public function testStyleDim(): void
    {
        $this->assertSame(
            "\033[2;37mTest\033[0m",
            Console::style('Test', style: Console::DIM)
        );
    }

    public function testStyleFlash(): void
    {
        $this->assertSame(
            "\033[5;37mTest\033[0m",
            Console::style('Test', style: Console::FLASH)
        );
    }

    public function testStyleItalic(): void
    {
        $this->assertSame(
            "\033[3;37mTest\033[0m",
            Console::style('Test', style: Console::ITALIC)
        );
    }

    public function testStyleUnderline(): void
    {
        $this->assertSame(
            "\033[4;37mTest\033[0m",
            Console::style('Test', style: Console::UNDERLINE)
        );
    }

    public function testSuccess(): void
    {
        $this->console->success('Test');

        rewind($this->output);

        $this->assertSame(
            "\033[0;32mTest\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testTable(): void
    {
        $this->console->table([
            ['1', '2', '3'],
            ['Test', 'Value', '0'],
        ]);

        rewind($this->output);

        $this->assertSame(
            '+------+-------+---+'.PHP_EOL.
            '| 1    | 2     | 3 |'.PHP_EOL.
            '| Test | Value | 0 |'.PHP_EOL.
            '+------+-------+---+'.PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testTableColor(): void
    {
        $this->console->table([
            ['1', '2', '3'],
            [Console::style('Test', Console::BLUE), 'Value', '0'],
        ]);

        rewind($this->output);

        $this->assertSame(
            '+------+-------+---+'.PHP_EOL.
            '| 1    | 2     | 3 |'.PHP_EOL.
            "| \033[0;34mTest\033[0m | Value | 0 |".PHP_EOL.
            '+------+-------+---+'.PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testTableHeader(): void
    {
        $this->console->table([
            ['1', '2', '3'],
            ['Test', 'Value', '0'],
        ], [
            'A',
            'B',
            'C',
        ]);

        rewind($this->output);

        $this->assertSame(
            '+------+-------+---+'.PHP_EOL.
            '| A    | B     | C |'.PHP_EOL.
            '+------+-------+---+'.PHP_EOL.
            '| 1    | 2     | 3 |'.PHP_EOL.
            '| Test | Value | 0 |'.PHP_EOL.
            '+------+-------+---+'.PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testWarning(): void
    {
        $this->console->warning('Test');

        rewind($this->output);

        $this->assertSame(
            "\033[0;33mTest\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testWrap(): void
    {
        $this->assertSame(
            'This'.PHP_EOL.
            'is a'.PHP_EOL.
            'test'.PHP_EOL.
            'string',
            Console::wrap('This is a test string', 5)
        );
    }

    public function testWrite(): void
    {
        $this->console->write('Test');

        rewind($this->output);

        $this->assertSame(
            'Test'.PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->input = fopen('php://memory', 'r+b');
        $this->output = fopen('php://memory', 'r+b');

        $this->console = new Console($this->input, $this->output, $this->output);
    }

    #[Override]
    protected function tearDown(): void
    {
        fclose($this->input);
        fclose($this->output);
    }
}
