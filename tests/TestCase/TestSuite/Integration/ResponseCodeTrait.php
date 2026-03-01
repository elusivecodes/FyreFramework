<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use PHPUnit\Framework\AssertionFailedError;

trait ResponseCodeTrait
{
    public function testResponseCode(): void
    {
        $this->get('/response');

        $this->assertResponseCode(200);
    }

    public function testResponseCodeFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response status code is equal to "200".');

        $this->get('/error');

        $this->assertResponseCode(200);
    }

    public function testResponseCodeNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertResponseCode(200);
    }

    public function testResponseError(): void
    {
        $this->get('/error');

        $this->assertResponseError();
    }

    public function testResponseErrorFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response status code is between 400 and 599.');

        $this->get('/response');

        $this->assertResponseError();
    }

    public function testResponseErrorNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertResponseError();
    }

    public function testResponseFailure(): void
    {
        $this->get('/fail');

        $this->assertResponseFailure();
    }

    public function testResponseFailureFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response status code is between 500 and 505.');

        $this->get('/response');

        $this->assertResponseFailure();
    }

    public function testResponseFailureNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertResponseFailure();
    }

    public function testResponseOk(): void
    {
        $this->get('/response');

        $this->assertResponseOk();
    }

    public function testResponseOkFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response status code is between 200 and 204.');

        $this->get('/error');

        $this->assertResponseOk();
    }

    public function testResponseOkNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertResponseOk();
    }

    public function testResponseSuccess(): void
    {
        $this->get('/response');

        $this->assertResponseSuccess();
    }

    public function testResponseSuccessFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response status code is between 200 and 308.');

        $this->get('/error');

        $this->assertResponseSuccess();
    }

    public function testResponseSuccessNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertResponseSuccess();
    }
}
