<?php

namespace App\Dto\Telegram\Message;

use App\Dto\Telegram\Message\Component\ButtonDto;
use App\Dto\Telegram\Message\Component\PhotoDto;
use App\Dto\Telegram\MessageDto;

class MessagePhotoDto extends MessageDto
{
    /**
     * @var PhotoDto[]
     */
    private array $photo;

    private ?string $caption;

    /**
     * @var ButtonDto[]|null
     */
    private ?array $buttons;

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

    public function setCaption(?string $caption): self
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
