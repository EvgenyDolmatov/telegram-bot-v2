<?php

namespace App\Commands;

use App\Repositories\RequestRepository;
use App\Services\SenderService;
use App\Services\TelegramService;
use Illuminate\Http\Request;

abstract class AbstractCommand
{
    protected RequestRepository $repository;
    protected SenderService $senderService;

    public function __construct(
        private readonly Request $request,
        private readonly TelegramService $telegramService,
    ) {
        $this->repository = new RequestRepository($this->request);
        $this->senderService = new SenderService($this->request, $this->telegramService);
    }

    abstract function execute(): void;
}
