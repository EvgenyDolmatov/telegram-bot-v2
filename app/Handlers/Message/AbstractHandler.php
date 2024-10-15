<?php

namespace App\Handlers\Message;

use App\Helpers\StepAction;
use App\Models\User;
use App\Repositories\RequestRepository;
use App\Services\SenderService;
use App\Services\TelegramService;
use Illuminate\Http\Request;

abstract class AbstractHandler
{
    protected StepAction $helper;
    protected User $user;
    protected SenderService $senderService;

    public function __construct(
        protected readonly TelegramService $telegramService,
        protected readonly Request $request
    ) {
        $this->helper = new StepAction($telegramService, $request);
        $this->senderService = new SenderService($request, $telegramService);

        $requestRepository = new RequestRepository($request);
        $this->user = User::getOrCreate($requestRepository);
    }

    abstract function handle(string $message): void;
}
