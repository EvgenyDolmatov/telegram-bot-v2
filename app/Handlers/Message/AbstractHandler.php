<?php

namespace App\Handlers\Message;

use App\Models\User;
use App\Repositories\RequestRepository;
use App\Services\TelegramService;
use Illuminate\Http\Request;

abstract class AbstractHandler
{
    protected User $user;

    public function __construct(
        protected readonly TelegramService $telegramService,
        protected readonly Request $request
    ) {
        $requestRepository = new RequestRepository($request);
        $this->user = User::getOrCreate($requestRepository);
    }

    abstract public function handle(string $message): void;
}
