<?php
declare(strict_types=1);

namespace Tests\Mock\Core\Loader;

class MockComposer
{
    /**
     * @return array<string, string>
     */
    public function getClassMap(): array
    {
        return [];
    }

    /**
     * @return array<string, string[]>
     */
    public function getPrefixesPsr4(): array
    {
        return [
            'Fyre' => [
                'src/',
            ],
        ];
    }
}

return new MockComposer();
