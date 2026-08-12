<?php
declare(strict_types=1);

namespace Fyre\Core\Make;

use InvalidArgumentException;

use function sprintf;

/**
 * Collects and saves generated files.
 */
class GenerationBatch
{
    /**
     * The generated files, indexed by destination path.
     *
     * @var array<string, GeneratedFile>
     */
    protected array $files = [];

    /**
     * Constructs a GenerationBatch.
     *
     * @param GeneratedFile ...$files The generated files.
     */
    public function __construct(GeneratedFile ...$files)
    {
        foreach ($files as $generatedFile) {
            $this->addFile($generatedFile);
        }
    }

    /**
     * Adds a file to the batch.
     *
     * @param GeneratedFile $generatedFile The generated file.
     *
     * @throws InvalidArgumentException If another file has the same destination.
     */
    public function addFile(GeneratedFile $generatedFile): void
    {
        $path = $generatedFile->getPath();

        if (isset($this->files[$path])) {
            throw new InvalidArgumentException(sprintf(
                'Generated file destination collision: `%s`.',
                $path
            ));
        }

        $this->files[$path] = $generatedFile;
    }

    /**
     * Saves every generated file.
     *
     * Every destination is checked before any file is saved.
     *
     * @param bool $force Whether existing files may be replaced.
     * @return bool Whether every file was saved.
     */
    public function save(bool $force = false): bool
    {
        foreach ($this->files as $generatedFile) {
            if (!$generatedFile->isValid($force)) {
                return false;
            }
        }

        foreach ($this->files as $generatedFile) {
            if (!$generatedFile->save()) {
                return false;
            }
        }

        return true;
    }
}
