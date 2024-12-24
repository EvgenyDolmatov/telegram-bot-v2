<?php

namespace App\Repositories\Telegram\Community;

use App\Dto\Telegram\Group\PermissionDto;
use App\Dto\Telegram\Group\PhotoDto;
use App\Dto\Telegram\GroupDto;
use App\Repositories\Telegram\Response\AbstractRepository;

class GroupRepository extends AbstractRepository
{
    /**
     * @throws \Exception
     */
    public function createDto(?array $data = null): GroupDto
    {
        try {
            $data = $this->payload;

            $dto = (new GroupDto())
                ->setId($data['id'])
                ->setTitle($data['title'])
                ->setUsername($data['username'])
                ->setType($data['type'])
                ->setActiveUsernames($data['active_usernames'] ?? null)
                ->setInviteLink($data['invite_link'])
                ->setPermissions($this->getPermissions($data['permissions']))
                ->setJoinToSendMessages($data['join_to_send_messages'])
                ->setPhoto($this->getPhoto($data['photo']))
                ->setMaxReactionCount($data['max_reaction_count'] ?? null)
                ->setAccentColorId($data['accent_color_id'] ?? null);
        } catch (\Throwable $exception) {
            throw new \Exception('Invalid channel response');
        }

        return $dto;
    }

    private function getPermissions(array $data): PermissionDto
    {
        return (new PermissionDto())
            ->setCanSendMessages($data['can_send_messages'])
            ->setCanSendMediaMessages($data['can_send_media_messages'])
            ->setCanSendAudios($data['can_send_audios'])
            ->setCanSendDocuments($data['can_send_documents'])
            ->setCanSendPhotos($data['can_send_photos'])
            ->setCanSendVideos($data['can_send_videos'])
            ->setCanSendVideoNotes($data['can_send_video_notes'])
            ->setCanSendVoiceNotes($data['can_send_voice_notes'])
            ->setCanSendPolls($data['can_send_polls'])
            ->setCanSendOtherMessages($data['can_send_other_messages'])
            ->setCanAddWebPagePreviews($data['can_add_web_page_previews'])
            ->setCanChangeInfo($data['can_change_info'])
            ->setCanInviteUsers($data['can_invite_users'])
            ->setCanPinMessages($data['can_pin_messages'])
            ->setCanManageTopics($data['can_manage_topics']);
    }

    private function getPhoto(array $data): PhotoDto
    {
        return (new PhotoDto())
            ->setSmallFileId($data['small_file_id'])
            ->setSmallFileUniqueId($data['small_file_unique_id'])
            ->setBigFileId($data['big_file_id'])
            ->setBigFileUniqueId($data['big_file_unique_id']);
    }
}
