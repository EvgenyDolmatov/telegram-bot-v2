<?php

namespace App\Handlers\Message;

use App\Helpers\StepAction;
use App\Models\User;
use App\Repositories\RequestRepository;
use App\Services\TelegramService;
use Illuminate\Http\Request;

abstract class AbstractHandler
{
    protected StepAction $helper;
    protected User $user;

    /**
     * @throws \Exception
     */
    public function __construct(
        protected readonly TelegramService $telegramService,
        protected readonly Request $request
    ) {
        $this->helper = new StepAction($telegramService, $request);

        $requestRepository = new RequestRepository($request);
        $this->user = User::getOrCreate($requestRepository);
    }

    abstract public function handle(string $message): void;
}
