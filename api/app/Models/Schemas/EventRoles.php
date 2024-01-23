<?php

namespace App\Models\Schemas;

use Illuminate\Database\Eloquent\Model;
use App\Models\Event;
use App\Models\WPUser;

/**
 * Event Roles information model
 *
 * @OA\Schema()
 */
class EventRoles
{
    /**
     * List of associated Event roles
     * 
     * @var EventRole[]
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="EventRole")
     * )
     */
    public ?array $roles = null;

    /**
     * List of eligible users
     * 
     * @var User[]
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="User")
     * )
     */
    public ?array $users = null;
    
    public function __construct(?Event $event = null)
    {
        if (!empty($event)) {
            $this->roles = [];
            foreach ($event->roles as $eventRole) {
                $this->roles[] = new EventRole($eventRole);
            }

            $users = WPUser::orderBy('display_name')->get();
            $this->users = [];
            foreach ($users as $user) {
                $this->users[] = new User($user);
            }
        }
    }
}
