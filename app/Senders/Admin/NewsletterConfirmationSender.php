<?php

namespace App\Senders\Admin;

use App\Enums\StateEnum;
use App\Models\Newsletter;
use App\Senders\AbstractSender;
use Illuminate\Support\Facades\Log;

class NewsletterConfirmationSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        if (!$this->user->isAdmin()) {
            $this->someProblemMessage();
            return;
        }

        $newsletter = Newsletter::create($this->prepareNewsletterData());

        $this->sendPreviewMessage($newsletter);
        $this->sendMessage(
            text: StateEnum::AdminNewsletterConfirmation->title(),
            buttons: StateEnum::AdminNewsletterConfirmation->buttons()
        );
    }

    private function prepareNewsletterData(): array
    {
        $originalPhotoPath = $this->uploadPhoto();
        $newsletterData = [
            'user_id' => $this->user->id,
            'text' => $this->getInputText()
        ];

        if ($originalPhotoPath) {
            $newsletterData['image'] = 'uploads/' . $originalPhotoPath;
        }

        return $newsletterData;
    }

    private function uploadPhoto(): string
    {
        $images = $this->getMessageDto()->getPhoto();
        $originalPhotoId = (end($images))->getFileId();

        return $this->senderService->uploadPhoto($originalPhotoId);
    }

    private function sendPreviewMessage(Newsletter $newsletter): void
    {
        Log::debug(asset($newsletter->image));

        if ($newsletter->image) {
            $this->sendPhoto(
                imageUrl: asset($newsletter->image),
                text: $newsletter->text,
                isTrash: false
            );
            return;
        }

        $this->sendMessage(
            text: $newsletter->text,
            isTrash: false
        );
    }
}
