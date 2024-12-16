<?php

namespace App\Dto\Telegram;

use App\Repositories\Telegram\AbstractRepository;
use App\Repositories\Telegram\CallbackQueryRepository;
use App\Repositories\Telegram\MessageRepository;
use Illuminate\Http\Request;

class RequestStrategy
{
    private AbstractRepository $repository;

    /**
     * @throws \Exception
     */
    public function defineMessageRepository(Request $request): AbstractRepository
    {
        if ($request->has('callback_query')) {
            $repository = (new CallbackQueryRepository($request));
        }

        if ($request->has('message')) {
            $repository = (new MessageRepository($request));
        }

        if (!isset($repository)) {
            throw new \Exception('Request data is unavailable.');
        }

        return $this->setRepository($repository);
    }

    public function setRepository(AbstractRepository $repository): AbstractRepository
    {
        $this->repository = $repository;

        return $this->repository;
    }

    public function getDto(): mixed
    {
        return $this->repository->getDto();
    }
}
