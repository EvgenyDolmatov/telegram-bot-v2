<?php

namespace App\Dto\Message;

class PhotoDto
{
    private string $fileId;
    private string $fileUniqueId;
    private int $fileSize;
    private int $width;
    private int $height;

    public function __construct(string $fileId, string $fileUniqueId, int $fileSize, int $width, int $height)
    {
        $this->fileId = $fileId;
        $this->fileUniqueId = $fileUniqueId;
        $this->fileSize = $fileSize;
        $this->width = $width;
        $this->height = $height;
    }

    public function getFileId(): string
    {
        return $this->fileId;
    }

    public function getFileUniqueId(): string
    {
        return $this->fileUniqueId;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }
}
