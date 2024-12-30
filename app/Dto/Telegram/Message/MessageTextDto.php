<?php

namespace App\Dto\Telegram\Message;

use App\Dto\Telegram\Message\Component\ButtonDto;
use App\Dto\Telegram\MessageDto;

class MessageTextDto extends MessageDto
{
    private string $text;

    /**
     * @var ButtonDto[]|null
     */
    private ?array $buttons;

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

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
