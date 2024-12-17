<?php

namespace App\Repositories\Telegram;

use App\Dto\Telegram\MessagePhotoDto;
use Illuminate\Http\Request;

class MessagePhotoRepository implements RepositoryInterface
{
    public function __construct(private Request $request) {}

    public function createDto(?array $data = null): MessagePhotoDto
    {
        return new MessagePhotoDto();
    }
}
