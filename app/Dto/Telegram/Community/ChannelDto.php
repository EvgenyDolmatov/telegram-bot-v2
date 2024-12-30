<?php

namespace App\Dto\Telegram\Community;

use App\Dto\Telegram\CommunityDto;

class ChannelDto extends CommunityDto
{
    private bool $isHasVisibleHistory;
    private bool $isCanSendPaidMedia;
    private array $availableReactions;

    public function getIsHasVisibleHistory(): bool
    {
        return $this->isHasVisibleHistory;
    }

    public function setIsHasVisibleHistory(bool $isHasVisibleHistory): self
    {
        $this->isHasVisibleHistory = $isHasVisibleHistory;

        return $this;
    }

    public function getIsCanSendPaidMedia(): bool
    {
        return $this->isCanSendPaidMedia;
    }

    public function setIsCanSendPaidMedia(bool $isCanSendPaidMedia): self
    {
        $this->isCanSendPaidMedia = $isCanSendPaidMedia;

        return $this;
    }

    public function getAvailableReactions(): array
    {
        return $this->availableReactions;
    }

    public function setAvailableReactions(array $availableReactions): self
    {
        $this->availableReactions = $availableReactions;

        return $this;
    }
}
