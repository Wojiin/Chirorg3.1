<?php

namespace App\Service;

use App\Error\ErrorMessage;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/** Valide et stocke les illustrations des fiches techniques. */
final readonly class TechnicalSheetImageStorage
{
    private const int MAX_FILE_SIZE = 5 * 1024 * 1024;
    private const string RELATIVE_DIRECTORY = '/uploads/fiches-techniques';
    private const array ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        private Filesystem $filesystem,
    ) {
    }

    public function store(mixed $image): string
    {
        if (!$image instanceof UploadedFile || !$image->isValid()) {
            throw new UnprocessableEntityHttpException(ErrorMessage::IMAGE_REQUIRED);
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($image->getPathname());
        if (!is_string($mimeType) || !isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
            throw new UnprocessableEntityHttpException(ErrorMessage::IMAGE_TYPE_INVALID);
        }

        if ($image->getSize() > self::MAX_FILE_SIZE) {
            throw new UnprocessableEntityHttpException(ErrorMessage::IMAGE_TOO_LARGE);
        }

        $targetDirectory = $this->projectDir.'/public'.self::RELATIVE_DIRECTORY;
        $this->filesystem->mkdir($targetDirectory, 0775);
        $filename = bin2hex(random_bytes(16)).'.'.self::ALLOWED_MIME_TYPES[$mimeType];

        try {
            $image->move($targetDirectory, $filename);
        } catch (FileException) {
            throw new HttpException(500, ErrorMessage::IMAGE_STORAGE_FAILED);
        }

        return self::RELATIVE_DIRECTORY.'/'.$filename;
    }
}
