<?php

namespace App\Dto\Telegram;

use App\Dto\Telegram\Group\PermissionDto;
use App\Dto\Telegram\Group\PhotoDto;

class GroupDto
{
    private int $id;
    private string $title;
    private string $username;
    private string $type;
    private array $activeUsernames;
    private string $inviteLink;
    private PermissionDto $permissions;
    private bool $joinToSendMessages;
    private ?PhotoDto $photo;
    private int $maxReactionCount;
    private int $accentColorId;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getActiveUsernames(): array
    {
        return $this->activeUsernames;
    }

    public function setActiveUsernames(array $activeUsernames): self
    {
        $this->activeUsernames = $activeUsernames;

        return $this;
    }

    public function getInviteLink(): string
    {
        return $this->inviteLink;
    }

    public function setInviteLink(string $inviteLink): self
    {
        $this->inviteLink = $inviteLink;

        return $this;
    }

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

    public function getMaxReactionCount(): int
    {
        return $this->maxReactionCount;
    }

    public function setMaxReactionCount(int $maxReactionCount): self
    {
        $this->maxReactionCount = $maxReactionCount;

        return $this;
    }

    public function getAccentColorId(): int
    {
        return $this->accentColorId;
    }

    public function setAccentColorId(int $accentColorId): self
    {
        $this->accentColorId = $accentColorId;

        return $this;
    }
}
