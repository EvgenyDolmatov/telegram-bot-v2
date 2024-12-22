<?php

namespace App\Repositories\Telegram\Response;

use App\Dto\Telegram\ChannelDto;

class ChannelRepository extends AbstractRepository
{
    /**
     * @throws \Exception
     */
    public function createDto(?array $data = null): ChannelDto
    {
        try {
            $data = $this->payload;

            $dto = (new ChannelDto())
                ->setId($data['id'] ?? null)
                ->setTitle($data['title'] ?? null)
                ->setUsername($data['username'] ?? null)
                ->setType($data['type'] ?? null)
                ->setActiveUsernames($data['active_usernames'] ?? null)
                ->setInviteLink($data['invite_link'] ?? null)
                ->setIsHasVisibleHistory($data['has_visible_history'] ?? null)
                ->setIsCanSendPaidMedia($data['can_send_paid_media'] ?? null)
                ->setAvailableReactions($data['available_reactions'] ?? null)
                ->setMaxReactionCount($data['max_reaction_count'] ?? null)
                ->setAccentColorId($data['accent_color_id'] ?? null);
        } catch (\Throwable $exception) {
            throw new \Exception('Invalid channel response');
        }

        return $dto;
    }
}
