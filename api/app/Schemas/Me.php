<?php

namespace App\Schemas;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\EVFUser;
use App\Models\Event;

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
    public ?string $username;

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
    public ?array $credentials;
    
    public function __construct(?Model $user = null, ?Event $event = null)
    {
        $this->token = csrf_token();
        if (empty($user) || !object_implements($user, EVFUser::class)) {
            $this->status = false;
        }
        else {
            $this->status = true;
            $this->username = $user->getAuthName();
            $this->credentials = $user->getAuthRoles($event ?? new Event());
        }
    }
}