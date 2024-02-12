<?php

namespace App\Models\Schemas;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\EVFUser;
use App\Models\Event;
use App\Support\Services\DefaultCountryService;
use App\Support\Services\DefaultEventService;

/**
 * Basic return value
 *
 * @OA\Schema()
 */
class Me
{
    /**
     * Login status value
     *
     * @var bool
     * @OA\Property()
     *
     */
    public bool $status;

    /**
     * Session CSRF token value. This value needs to be passed to every POST request to prevent cross-posting
     * of API calls. It is similar to the session Cookie, but that cookie is automatically passed along to the
     * server. The CSRF token is provided by the server and would not be visible to an attacker, nor sent
     * automatically.
     *
     * @var string
     * @OA\Property()
     */
    public ?string $token;

    /**
     * User name as defined in the back-end
     *
     * @var string
     * @OA\Property()
     */
    public ?string $username = null;

    /**
     * Country associated with this user
     *
     * @var int
     * @OA\Property()
     */
    public ?int $countryId = null;

    /**
     * Event associated with this session
     *
     * @var int
     * @OA\Property()
     */
    public ?int $eventId = null;

    /**
     * Credentials defined in the back-end system. This should be an indication about what
     * operations are allowed by the back-end system and which are not.
     *
     * @var string[]
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="string")
     * )
     */
    public ?array $credentials = null;
    
    public function __construct(?Model $user = null, ?Event $event = null)
    {
        $this->token = csrf_token();
        if (empty($user) || !object_implements($user, EVFUser::class)) {
            $this->status = false;
        }
        else {
            $this->status = true;
            $this->username = $user->getAuthName();
            $this->credentials = $user->getAuthRoles($event);

            // For HoDs, set the default country for all interactions
            // The countryObject is also influenced by a ?country=<value> parameter,
            // but we recheck if the user actually has the hod:<country-id> role as well
            $country = DefaultCountryService::determineCountry($user);
            if (!empty($country)) {
                $this->countryId = $country->getKey();
            }

            // For users linked to a specific event, set the event
            $event = DefaultEventService::determineEvent($user);
            if (!empty($event)) {
                $this->eventId = $event->getKey();
            }
        }
    }
}
