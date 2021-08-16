<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\Module;

use Codeception\Module;
use Codeception\Module\Symfony;
use Codeception\TestInterface;
use DirectoryIterator;
use FSi\Component\Files\Upload\FileFactory;
use FSi\Component\Files\WebFile;

use function codecept_data_dir;
use function file_exists;
use function is_dir;
use function rmdir;
use function sprintf;
use function unlink;

final class FileModule extends Module
{
    private FileFactory $fileFactory;

    /**
     * @phpcs:disable
     * @param array<string, mixed> $settings
     */
    public function _beforeSuite($settings = []): void
    {
        $this->clearUploadedTestFiles();
    }

    /**
     * @phpcs:disable
     */
    public function _afterSuite(): void
    {
        $this->clearUploadedTestFiles();
    }

    /**
     * @phpcs:disable
     * @param TestInterface $test
     */
    public function _before(TestInterface $test): void
    {
        /** @var Symfony $symfony */
        $symfony = $this->getModule('Symfony');

        /** @var FileFactory $fileFactory */
        $fileFactory = $symfony->grabService(FileFactory::class);
        $this->fileFactory = $fileFactory;
    }

    public function createTestWebFile(string $filename = 'test.jpg'): WebFile
    {
        return $this->fileFactory->createFromPath(codecept_data_dir($filename));
    }

    private function clearUploadedTestFiles(): void
    {
        $this->clearDirectoryIfExists('article', true);
        $this->clearDirectoryIfExists('banner', true);
    }

    private function clearDirectoryIfExists(string $path, bool $prefixWithFullPath): void
    {
        if (true === $prefixWithFullPath) {
            $path = sprintf('%s/../project/public/files/%s', __DIR__, $path);
        }

        if (false === is_dir($path) && false === file_exists($path)) {
            return;
        }

        $iterator = new DirectoryIterator($path);
        foreach ($iterator as $file) {
            if (true === $file->isDot()) {
                continue;
            }

            if (true === $file->isDir()) {
                $this->clearDirectoryIfExists($file->getPathname(), false);
            } elseif (true === $file->isFile()) {
                unlink($file->getPathname());
            }
        }

        rmdir($path);
    }
}
