<?php

namespace App\Support\Services\Codes;

use App\Models\Event;
use App\Models\Schemas\CodeProcessStatus;

class CodeService
{
    public ?Event $event;
    public $errors = [];
    public CodeProcessStatus $result;

    public function __construct(?Event $event)
    {
        $this->event = $event;
        $this->result = new CodeProcessStatus();
        $this->result->status = "error";
        $this->result->action = "fail";
    }

    public function setEvent(Event $event)
    {
        $this->event = $event;
        $this->result->eventId = $event->getKey();
    }

    public function addError($msg)
    {
        \Log::error($msg);
        $this->errors[] = $msg;
        $this->manager->result->status = 'error';
        return false; // allow to return this return code as failure directly
    }

    public function handle(string $action, array $codes)
    {
        switch ($action) {
            case 'login':
                \Log::debug("login service");
                $this->result->action = "login";
                return (new LoginService($this))->handle($codes);
            case 'badge':
                \Log::debug("badge service");
                $this->result->action = "badge";
                return (new BadgeService($this))->handle($codes);
            default:
                \Log::error("Invalid Accreditation code action '$action' with codes " . json_encode($codes));
                $this->addError("Invalid action");
                break;
        }
        return false;
    }
}
