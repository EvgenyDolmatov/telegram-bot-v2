<?php

namespace App\Dto\Telegram\Community\Component;

class PermissionDto
{
    private bool $canSendMessages;
    private bool $canSendMediaMessages;
    private bool $canSendAudios;
    private bool $canSendDocuments;
    private bool $canSendPhotos;
    private bool $canSendVideos;
    private bool $canSendVideoNotes;
    private bool $canSendVoiceNotes;
    private bool $canSendPolls;
    private bool $canSendOtherMessages;
    private bool $canAddWebPagePreviews;
    private bool $canChangeInfo;
    private bool $canInviteUsers;
    private bool $canPinMessages;
    private bool $canManageTopics;

    public function getCanSendMessages(): bool
    {
        return $this->canSendMessages;
    }

    public function setCanSendMessages(bool $canSendMessages): self
    {
        $this->canSendMessages = $canSendMessages;

        return $this;
    }

    public function getCanSendMediaMessages(): bool
    {
        return $this->canSendMediaMessages;
    }

    public function setCanSendMediaMessages(bool $canSendMediaMessages): self
    {
        $this->canSendMediaMessages = $canSendMediaMessages;

        return $this;
    }

    public function getCanSendAudios(): bool
    {
        return $this->canSendAudios;
    }

    public function setCanSendAudios(bool $canSendAudios): self
    {
        $this->canSendAudios = $canSendAudios;

        return $this;
    }

    public function getCanSendDocuments(): bool
    {
        return $this->canSendDocuments;
    }

    public function setCanSendDocuments(bool $canSendDocuments): self
    {
        $this->canSendDocuments = $canSendDocuments;

        return $this;
    }

    public function getCanSendPhotos(): bool
    {
        return $this->canSendPhotos;
    }

    public function setCanSendPhotos(bool $canSendPhotos): self
    {
        $this->canSendPhotos = $canSendPhotos;

        return $this;
    }

    public function getCanSendVideos(): bool
    {
        return $this->canSendVideos;
    }

    public function setCanSendVideos(bool $canSendVideos): self
    {
        $this->canSendVideos = $canSendVideos;

        return $this;
    }

    public function getCanSendVideoNotes(): bool
    {
        return $this->canSendVideoNotes;
    }

    public function setCanSendVideoNotes(bool $canSendVideoNotes): self
    {
        $this->canSendVideoNotes = $canSendVideoNotes;

        return $this;
    }

    public function getCanSendVoiceNotes(): bool
    {
        return $this->canSendVoiceNotes;
    }

    public function setCanSendVoiceNotes(bool $canSendVoiceNotes): self
    {
        $this->canSendVoiceNotes = $canSendVoiceNotes;

        return $this;
    }

    public function getCanSendPolls(): bool
    {
        return $this->canSendPolls;
    }

    public function setCanSendPolls(bool $canSendPolls): self
    {
        $this->canSendPolls = $canSendPolls;

        return $this;
    }

    public function getCanSendOtherMessages(): bool
    {
        return $this->canSendOtherMessages;
    }

    public function setCanSendOtherMessages(bool $canSendOtherMessages): self
    {
        $this->canSendOtherMessages = $canSendOtherMessages;

        return $this;
    }

    public function getCanAddWebPagePreviews(): bool
    {
        return $this->canAddWebPagePreviews;
    }

    public function setCanAddWebPagePreviews(bool $canAddWebPagePreviews): self
    {
        $this->canAddWebPagePreviews = $canAddWebPagePreviews;

        return $this;
    }

    public function getCanChangeInfo(): bool
    {
        return $this->canChangeInfo;
    }

    public function setCanChangeInfo(bool $canChangeInfo): self
    {
        $this->canChangeInfo = $canChangeInfo;

        return $this;
    }

    public function getCanInviteUsers(): bool
    {
        return $this->canInviteUsers;
    }

    public function setCanInviteUsers(bool $canInviteUsers): self
    {
        $this->canInviteUsers = $canInviteUsers;

        return $this;
    }

    public function getCanPinMessages(): bool
    {
        return $this->canPinMessages;
    }

    public function setCanPinMessages(bool $canPinMessages): self
    {
        $this->canPinMessages = $canPinMessages;

        return $this;
    }

    public function getCanManageTopics(): bool
    {
        return $this->canManageTopics;
    }

    public function setCanManageTopics(bool $canManageTopics): self
    {
        $this->canManageTopics = $canManageTopics;

        return $this;
    }
}
