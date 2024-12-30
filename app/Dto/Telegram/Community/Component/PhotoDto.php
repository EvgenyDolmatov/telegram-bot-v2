<?php

namespace App\Dto\Telegram\Community\Component;

class PhotoDto
{
    private string $smallFileId;
    private string $smallFileUniqueId;
    private string $bigFileId;
    private string $bigFileUniqueId;

    public function getSmallFileId(): string
    {
        return $this->smallFileId;
    }

    public function setSmallFileId(string $smallFileId): self
    {
        $this->smallFileId = $smallFileId;

        return $this;
    }

    public function getSmallFileUniqueId(): string
    {
        return $this->smallFileUniqueId;
    }

    public function setSmallFileUniqueId(string $smallFileUniqueId): self
    {
        $this->smallFileUniqueId = $smallFileUniqueId;

        return $this;
    }

    public function getBigFileId(): string
    {
        return $this->bigFileId;
    }

    public function setBigFileId(string $bigFileId): self
    {
        $this->bigFileId = $bigFileId;

        return $this;
    }

    public function getBigFileUniqueId(): string
    {
        return $this->bigFileUniqueId;
    }

    public function setBigFileUniqueId(string $bigFileUniqueId): self
    {
        $this->bigFileUniqueId = $bigFileUniqueId;

        return $this;
    }
}
