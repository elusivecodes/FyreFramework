<?php
declare(strict_types=1);

use Fyre\Http\ClientResponse;
use Fyre\Http\DownloadResponse;
use Tests\Mock\Controllers\TestController;

$router->get('test', TestController::class, as: 'test');
$router->get('test/{id}', TestController::class, as: 'test2');

$router->get('response', static function(): string {
    return 'This is a test response';
});

$router->get('empty', static function(): string {
    return '';
});

$router->get('cookie', static function(): ClientResponse {
    return response()->withCookie('key', 'value');
});

$router->get('header', static function(): ClientResponse {
    return response()->withHeader('Name', 'This is a header value');
});

$router->get('redirect', static function(): ClientResponse {
    return redirect('/test');
});

$router->get('download', static function(): DownloadResponse {
    return DownloadResponse::createFromFile('tests/assets/test.jpg');
});

$router->get('session', static function(): string {
    $_SESSION['key'] = 'value';

    return '';
});

$router->get('flash', static function(): string {
    session()->setFlash('key', 'value');

    return '';
});

$router->get('error', static function(): void {
    abort(404);
});

$router->get('fail', static function(): void {
    abort(500);
});
