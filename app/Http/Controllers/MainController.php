<?php

namespace App\Http\Controllers;

use App\Handlers\HandlerStrategy;
use App\Repositories\Telegram\Request\RequestStrategy;
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

        $requestStrategy = new RequestStrategy($request);
        $repository = $requestStrategy->defineRepository();

        // Prepare message to delete on next step
        if (method_exists($repository->createDto(), 'getChat')) {
            $repository->addToTrash();
        }

        $handlerStrategy = new HandlerStrategy($telegram, $repository);
        $handlerStrategy->defineBehavior();
    }
}
