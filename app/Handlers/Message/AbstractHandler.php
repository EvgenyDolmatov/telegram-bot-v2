<?php

namespace App\Handlers\Message;

use App\Enums\CommandEnum;
use App\Helpers\StepAction;
use App\Models\State;
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

    protected function getUserStateCode(): string
    {
        $userState = $this->user->states()->first();

        if (!$userState) {
            $userState = State::where('code', CommandEnum::START)->first();
            $this->user->states()->attach($userState->id);
        }

        return $userState->code;
    }
}
