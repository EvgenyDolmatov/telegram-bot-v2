<?php

namespace App\Dto;

use App\Dto\Message\PhotoDto;

class Message1Dto
{
    private int $id;
    private ?string $text;
    private ?PhotoDto $photo;

    public function __construct(int $id, ?string $text = null, ?PhotoDto $photo = null)
    {
        $this->id = $id;
        $this->text = $text ?? '';
        $this->photo = $photo;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getPhoto(): ?PhotoDto
    {
        return $this->photo;
    }
}
