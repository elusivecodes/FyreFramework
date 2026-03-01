<?php
declare(strict_types=1);

namespace Tests\Mock\Controllers;

class TestController
{
    public function index(): string
    {
        return 'This is a test response';
    }

    public function test(): string
    {
        return '';
    }
}
