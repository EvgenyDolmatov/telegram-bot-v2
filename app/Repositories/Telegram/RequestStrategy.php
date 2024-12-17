<?php

namespace App\Repositories\Telegram;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

readonly class RequestStrategy
{
    private RepositoryInterface $repository;

    public function __construct(
        private Request $request
    ) {
    }

    public function setRepository(RepositoryInterface $repository): RepositoryInterface
    {
        $this->repository = $repository;

        return $repository;
    }

    /**
     * @throws \Exception
     */
    public function defineRepository(): RepositoryInterface
    {


        if ($this->request->has('callback_query')) {
            $repository = new CallbackRepository();
        }

        if ($this->request->has('message')) {
            $messageData = $this->request->get('message');

            if (array_key_exists('text', $messageData)) {
                Log::debug('RequestStrategy.php: text: ' . json_encode($this->request->all()));
                $repository = new MessageTextRepository();
            }

            if (array_key_exists('photo', $messageData)) {
                $repository = new MessagePhotoRepository();
            }
        }

        if (!isset($repository)) {
            throw new \Exception('Request data is unavailable.');
        }

        return $this->setRepository($repository);
    }

    public function createDto()
    {
        return $this->repository->createDto();
    }
}
