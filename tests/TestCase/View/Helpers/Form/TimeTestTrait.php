<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

use Closure;
use Fyre\Utility\DateTime\Time;
use Fyre\View\View;

trait TimeTestTrait
{
    public function testTime(): void
    {
        $this->assertSame(
            '<input id="time-value" name="time_value" type="time" />',
            $this->view->Form->time('time_value')
        );
    }

    public function testTimeAttributeArray(): void
    {
        $this->assertSame(
            '<input id="time" name="time" data-test="[1,2]" type="time" />',
            $this->view->Form->time('time', [
                'data-test' => [1, 2],
            ])
        );
    }

    public function testTimeAttributeEscape(): void
    {
        $this->assertSame(
            '<input id="time" name="time" data-test="&lt;test&gt;" type="time" />',
            $this->view->Form->time('time', [
                'data-test' => '<test>',
            ])
        );
    }

    public function testTimeAttributeInvalid(): void
    {
        $this->assertSame(
            '<input class="test" id="time" name="time" type="time" />',
            $this->view->Form->time('time', [
                '*class*' => 'test',
            ])
        );
    }

    public function testTimeAttributes(): void
    {
        $this->assertSame(
            '<input class="test" id="other" name="time" type="time" />',
            $this->view->Form->time('time', [
                'class' => 'test',
                'id' => 'other',
            ])
        );
    }

    public function testTimeAttributesOrder(): void
    {
        $this->assertSame(
            '<input class="test" id="other" name="time" type="time" />',
            $this->view->Form->time('time', [
                'id' => 'other',
                'class' => 'test',
            ])
        );
    }

    public function testTimeDot(): void
    {
        $this->assertSame(
            '<input id="key-time-value" name="key[time_value]" type="time" />',
            $this->view->Form->time('key.time_value')
        );
    }

    public function testTimeDotDeep(): void
    {
        $this->assertSame(
            '<input id="deep-key-time-value" name="deep[key][time_value]" type="time" />',
            $this->view->Form->time('deep.key.time_value')
        );
    }

    public function testTimeId(): void
    {
        $this->assertSame(
            '<input id="other" name="time" type="time" />',
            $this->view->Form->time('time', [
                'id' => 'other',
            ])
        );
    }

    public function testTimeIdFalse(): void
    {
        $this->assertSame(
            '<input name="time" type="time" />',
            $this->view->Form->time('time', [
                'id' => false,
            ])
        );
    }

    public function testTimeIdPrefix(): void
    {
        $this->view->Form->open(idPrefix: 'test');

        $this->assertSame(
            '<input id="test-time" name="time" type="time" />',
            $this->view->Form->time('time')
        );
    }

    public function testTimeName(): void
    {
        $this->assertSame(
            '<input id="time" name="other" type="time" />',
            $this->view->Form->time('time', [
                'name' => 'other',
            ])
        );
    }

    public function testTimeNameFalse(): void
    {
        $this->assertSame(
            '<input id="time" type="time" />',
            $this->view->Form->time('time', [
                'name' => false,
            ])
        );
    }

    public function testTimeValueDefault(): void
    {
        $time = Time::createFromArray([0, 0]);

        $this->assertSame(
            '<input id="time" name="time" type="time" value="00:00" />',
            $this->view->Form->time('time', [
                'default' => $time,
            ])
        );
    }

    public function testTimeValuePost(): void
    {
        Closure::bind(function(): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody([
                'time' => '00:00',
            ]);
        }, $this->view, View::class)();

        $this->assertSame(
            '<input id="time" name="time" type="time" value="00:00" />',
            $this->view->Form->time('time')
        );
    }

    public function testTimeValuePostDot(): void
    {
        Closure::bind(function(): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody([
                'key' => [
                    'time' => '00:00',
                ],
            ]);
        }, $this->view, View::class)();

        $this->assertSame(
            '<input id="key-time" name="key[time]" type="time" value="00:00" />',
            $this->view->Form->time('key.time')
        );
    }
}
