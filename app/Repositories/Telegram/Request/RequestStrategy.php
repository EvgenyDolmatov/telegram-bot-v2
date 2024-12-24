<?php

namespace App\Repositories\Telegram\Request;

use App\Repositories\Telegram\Response\PollRepository;
use Illuminate\Http\Request;

readonly class RequestStrategy
{
    private RepositoryInterface $repository;

    public function __construct(
        private Request $request
    ) {
    }

    public function setRepository(RepositoryInterface $repository): void
    {
        $this->repository = $repository;
    }

    /**
     * @throws \Exception
     */
    public function defineRepository(): RepositoryInterface
    {
        if ($this->request->has('callback_query')) {
            $callbackData = $this->request->get('callback_query');
            $repository = new CallbackRepository($callbackData);
        }

        if ($this->request->has('message')) {
            $messageData = $this->request->get('message');
            $repository = (new MessageRepository($messageData))->defineRepository();
        }

        if ($this->request->has('poll')) {
            $messageData = $this->request->get('poll');
            $repository = (new PollRepository($messageData));
        }

        if (!isset($repository)) {
            throw new \Exception('Request data is unavailable.');
        }

        $this->setRepository($repository);

        return $this->repository;
    }
}
