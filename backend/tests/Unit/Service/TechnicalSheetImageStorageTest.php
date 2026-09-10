<?php

namespace App\Tests\Unit\Service;

use App\Service\TechnicalSheetImageStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class TechnicalSheetImageStorageTest extends TestCase
{
    private string $projectDir;
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->projectDir = sys_get_temp_dir().'/chirorg-image-storage-'.bin2hex(random_bytes(8));
        $this->filesystem->mkdir($this->projectDir);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->projectDir);
    }

    public function testStoresAValidPngUsingAnOpaqueGeneratedName(): void
    {
        $source = $this->projectDir.'/source.png';
        file_put_contents($source, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true,
        ));
        $upload = new UploadedFile($source, 'bloc.png', 'image/png', null, true);

        $url = (new TechnicalSheetImageStorage($this->projectDir, $this->filesystem))->store($upload);

        self::assertMatchesRegularExpression('#^/uploads/fiches-techniques/[a-f0-9]{32}\.png$#', $url);
        self::assertFileExists($this->projectDir.'/public'.$url);
    }

    public function testRejectsMissingAndNonImageUploads(): void
    {
        $storage = new TechnicalSheetImageStorage($this->projectDir, $this->filesystem);

        try {
            $storage->store(null);
            self::fail('A missing image should be rejected.');
        } catch (UnprocessableEntityHttpException $exception) {
            self::assertSame('Une image valide est obligatoire.', $exception->getMessage());
        }

        $source = $this->projectDir.'/document.txt';
        file_put_contents($source, 'not an image');

        try {
            $storage->store(new UploadedFile($source, 'document.txt', 'text/plain', null, true));
            self::fail('A non-image file should be rejected.');
        } catch (UnprocessableEntityHttpException $exception) {
            self::assertSame('Seules les images JPEG, PNG et WebP sont acceptées.', $exception->getMessage());
        }
    }
}
