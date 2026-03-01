<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Console;

use Fyre\Console\Command;
use PHPUnit\Framework\AssertionFailedError;

trait ExitCodeTrait
{
    public function testExitCode(): void
    {
        $this->exitCode = 0;

        $this->assertExitCode(0);
    }

    public function testExitCodeFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that command exit code is equal to "1".');

        $this->exitCode = 0;

        $this->assertExitCode(1);
    }

    public function testExitError(): void
    {
        $this->exitCode = Command::CODE_ERROR;

        $this->assertExitError();
    }

    public function testExitErrorFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that command exit code is equal to "1".');

        $this->exitCode = Command::CODE_SUCCESS;

        $this->assertExitError();
    }

    public function testExitSuccess(): void
    {
        $this->exitCode = Command::CODE_SUCCESS;

        $this->assertExitSuccess();
    }

    public function testExitSuccessFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that command exit code is equal to "0".');

        $this->exitCode = Command::CODE_ERROR;

        $this->assertExitSuccess();
    }
}
