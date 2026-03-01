<?php
declare(strict_types=1);

namespace Tests\Mock\Controllers;

class HomeController
{
    public function altMethod(): string
    {
        return '';
    }

    public function index(): string
    {
        return '';
    }
}
