<?php

namespace App\Support\Services\Codes;

use App\Models\Event;
use App\Models\Schemas\CodeProcessStatus;

class CodeService
{
    public ?Event $event;
    public $errors = [];
    public CodeProcessStatus $result;

    public function __construct(?Event $event = null)
    {
        $this->result = new CodeProcessStatus();
        $this->result->status = "error";
        $this->result->action = "fail";
        $this->setEvent($event);
    }

    public function setEvent(?Event $event)
    {
        $this->event = $event;
        if (!empty($event) && $event->exists) {
            $this->result->eventId = $event->getKey();
        }
        else {
            $this->result->eventId = 0;
        }
    }

    public function addError($msg)
    {
        \Log::error($msg);
        $this->errors[] = $msg;
        $this->result->status = 'error';
        return false; // allow to return this return code as failure directly
    }

    public function handle(string $action, array $codes)
    {
        switch ($action) {
            case 'login':
                $this->result->action = "login";
                return (new LoginService($this))->handle($codes);
            case 'badge':
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
