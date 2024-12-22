<?php

namespace App\Http\Controllers;

use App\Handlers\MessageStrategy;
use App\Repositories\Tg\Request\RequestStrategy;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MainController extends Controller
{
    /**
     * @throws \Exception
     */
    public function webhook(Request $request): void
    {
        $telegram = new TelegramService();
        $telegram->resetQueue();

        Log::debug(json_encode($request->all()));

        $repository = (new RequestStrategy($request))->defineRepository();
//        $requestDto = $repository->createDto();

        // Prepare message to delete on next step
        $repository->addToTrash();

        $strategy = new MessageStrategy($telegram, $repository);
        $strategy->defineHandler()->process();
    }
}
