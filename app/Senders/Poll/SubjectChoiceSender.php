<?php

namespace App\Senders\Poll;

use App\Dto\Telegram\Message\Component\ButtonDto;
use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\Models\Sector;
use App\Models\Subject;
use App\Senders\AbstractSender;

class SubjectChoiceSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $subjects = $this->getSubjects();

        // TODO: Check !!!
        if (!$subjects || count($subjects) === 0) {
            $this->someProblemMessage();
            return;
        }

        $buttons = array_map(
            fn($subject) => new ButtonDto($subject['code'], $subject['title']),
            $subjects->toArray()
        );

        $buttons[] = new ButtonDto(CallbackEnum::Back->value, CallbackEnum::Back->buttonText());

        $this->sendMessage(StateEnum::PollSubjectChoice->title(), $buttons);
    }

    private function getSubjects()
    {
        $user = $this->user;
        $flowData = $user->getFlowData();

        if (!array_key_exists(StateEnum::PollSectorChoice->value, $flowData)) {
            return null;
        }

        // If pressed "back" button or unavailable value
        if ($this->getInputText() === CallbackEnum::Back->value
            || !Subject::where('code', $this->getInputText())->first()) {
            $sector = Sector::where('code', $flowData[StateEnum::PollSectorChoice->value])->first();
            return $sector->subjects()->where('parent_id', null)->get();
        }

        // Get children subjects
        if (array_key_exists(StateEnum::PollSubjectChoice->value, $flowData)) {
            return $this->getChildrenSubjects($flowData);
        }

        // Get subjects
        $sector = Sector::where('code', $flowData[StateEnum::PollSectorChoice->value])->first();
        return $sector ? $sector->subjects()->where('parent_id', null)->get(): null;
    }

    private function getChildrenSubjects(array $flowData)
    {
        $parentSubject = Subject::where('code', $flowData[StateEnum::PollSubjectChoice->value])->first();

        return $parentSubject && $parentSubject->has_child
            ? Subject::where('parent_id', $parentSubject->id)->get()
            : null;
    }
}
