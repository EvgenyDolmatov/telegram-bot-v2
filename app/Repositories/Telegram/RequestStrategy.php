<?php

namespace App\Repositories\Telegram;

use App\Repositories\Telegram\Message\MessagePhotoRepository;
use App\Repositories\Telegram\Message\MessageTextRepository;
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
            $repository = new CallbackRepository($this->request);
        }

        if ($this->request->has('message')) {
            $messageData = $this->request->get('message');

            if (array_key_exists('text', $messageData)) {
                $repository = new MessageTextRepository($this->request);
            }

            if (array_key_exists('photo', $messageData)) {
                $repository = new MessagePhotoRepository($this->request);
            }
        }

        if (!isset($repository)) {
            throw new \Exception('Request data is unavailable.');
        }

        $this->setRepository($repository);

        return $this->repository;
    }
}
