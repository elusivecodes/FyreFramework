<?php
declare(strict_types=1);

namespace Tests\Mock\Http;

use Fyre\Http\Cookie\CookieJar;

class TestCookieJar extends CookieJar
{
    protected const MAX_COOKIES = 3;

    protected const MAX_COOKIES_PER_DOMAIN = 3;
}
