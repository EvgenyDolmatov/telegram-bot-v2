<?php

namespace App\Dto\Telegram;

use App\Dto\ButtonDto;
use App\Dto\Telegram\Message\ChatDto;
use App\Dto\Telegram\Message\FromDto;
use App\Dto\Telegram\Message\PhotoDto;

class MessagePhotoDto
{
    private int $id;

    private FromDto $from;

    /**
     * @var ChatDto
     */
    private ChatDto $chat;

    /**
     * @var int
     */
    private int $date;

    /**
     * @var PhotoDto[]
     */
    private array $photo;

    private ?string $caption;

    /**
     * @var ButtonDto[]|null
     */
    private ?array $buttons;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getFrom(): FromDto
    {
        return $this->from;
    }

    public function setFrom(FromDto $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getChat(): ChatDto
    {
        return $this->chat;
    }

    public function setChat(ChatDto $chat): self
    {
        $this->chat = $chat;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return PhotoDto[]
     */
    public function getPhoto(): array
    {
        return $this->photo;
    }

    /**
     * @param PhotoDto[] $photo
     */
    public function setPhoto(array $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(?string $caption = null): self
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * @return ButtonDto[]|null
     */
    public function getButtons(): ?array
    {
        return $this->buttons;
    }

    /**
     * @param ButtonDto[]|null $buttons
     * @return $this
     */
    public function setButtons(?array $buttons): self
    {
        $this->buttons = $buttons;

        return $this;
    }
}
