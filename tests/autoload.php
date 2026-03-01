<?php
declare(strict_types=1);

namespace Tests\Mock\Core\Loader;

class MockComposer
{
    public function getClassMap(): array
    {
        return [];
    }

    public function getPrefixesPsr4(): array
    {
        return [
            'Fyre' => 'src/',
        ];
    }
}

return new MockComposer();
