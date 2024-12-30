<?php

namespace App\Dto\Telegram\Community;

use App\Dto\Telegram\Community\Component\PermissionDto;
use App\Dto\Telegram\Community\Component\PhotoDto;
use App\Dto\Telegram\CommunityDto;

class GroupDto extends CommunityDto
{
    private PermissionDto $permissions;
    private bool $joinToSendMessages;
    private ?PhotoDto $photo;

    public function getPermissions(): PermissionDto
    {
        return $this->permissions;
    }

    public function setPermissions(PermissionDto $permissions): self
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function getJoinToSendMessages(): bool
    {
        return $this->joinToSendMessages;
    }

    public function setJoinToSendMessages(bool $joinToSendMessages): self
    {
        $this->joinToSendMessages = $joinToSendMessages;

        return $this;
    }

    public function getPhoto(): ?PhotoDto
    {
        return $this->photo;
    }

    public function setPhoto(?PhotoDto $photo): self
    {
        $this->photo = $photo;

        return $this;
    }
}
