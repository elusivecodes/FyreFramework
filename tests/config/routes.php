<?php
declare(strict_types=1);

use Fyre\Http\ClientResponse;
use Fyre\Http\DownloadResponse;
use Fyre\Http\ServerRequest;
use Fyre\Router\Router;
use Psr\Http\Message\UploadedFileInterface;
use Tests\Mock\Controllers\TestController;

/** @var Router $router */
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

$router->post('csrf', static function(ServerRequest $request): string {
    return (string) $request->getParsedBody()['value'];
});

$router->connect(
    'data',
    static function(ServerRequest $request): string {
        return (string) json_encode($request->getParsedBody(), JSON_THROW_ON_ERROR);
    },
    methods: ['POST', 'PUT']
);

$router->post('upload', static function(ServerRequest $request): string {
    $file = $request->getUploadedFile('profile.avatar');

    if ($file === null) {
        return 'No file.';
    }

    if (!($file instanceof UploadedFileInterface)) {
        throw new RuntimeException('Uploaded file is not valid.');
    }

    return (string) json_encode([
        'contentType' => $request->getHeaderLine('Content-Type'),
        'filename' => $file->getClientFilename(),
        'mediaType' => $file->getClientMediaType(),
        'contents' => (string) $file->getStream(),
        'data' => $request->getParsedBody(),
    ], JSON_THROW_ON_ERROR);
});

$router->post('upload/move', static function(ServerRequest $request): string {
    $file = $request->getUploadedFile('profile.avatar');

    if (!($file instanceof UploadedFileInterface)) {
        throw new RuntimeException('Uploaded file is not valid.');
    }

    $file->moveTo((string) $request->getParsedBody()['target']);

    return '';
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

$router->get('fail-extended', static function(): void {
    abort(507);
});
