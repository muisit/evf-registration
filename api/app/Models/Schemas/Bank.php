<?php

namespace App\Models\Schemas;

use Illuminate\Database\Eloquent\Model;
use App\Models\Event;

/**
 * Bank information model
 *
 * @OA\Schema()
 */
class Bank
{
    /**
     * Symbol for the currency required for paying for this event
     *
     * @var string
     * @OA\Property()
     */
    public ?string $symbol;

    /**
     * Currency name for the currency required for paying for this event
     *
     * @var string
     * @OA\Property()
     */
    public ?string $currency;

    /**
     * Name of the bank of the event organisation
     *
     * @var string
     * @OA\Property()
     */
    public ?string $bank;

    /**
     * Account name with the bank of the organisation
     *
     * @var string
     * @OA\Property()
     */
    public ?string $account;

    /**
     * Address of the organisation as far as the bank account is concerned
     *
     * @var string
     * @OA\Property()
     */
    public ?string $address;

    /**
     * IBAN code for the organisation bank account
     *
     * @var string
     * @OA\Property()
     */
    public ?string $iban;

    /**
     * SWIFT code for the bank of the organisation
     *
     * @var string
     * @OA\Property()
     */
    public ?string $swift;

    /**
     * Reference text for transfers to the organisation bank
     *
     * @var string
     * @OA\Property()
     */
    public ?string $reference;

    public function __construct(?Event $event = null)
    {
        if (!empty($event)) {
            $this->symbol = $event->event_currency_symbol;
            $this->currency = $event->event_currency_name;
            $this->baseFee = floatval($event->event_base_fee);
            $this->competitionFee = floatval($event->event_competition_fee);
            $this->bank = $event->bank;
            $this->account = $event->event_account_name;
            $this->address = $event->event_organisers_address;
            $this->iban = $event->event_iban;
            $this->swift = $event->event_swift;
            $this->reference = $event->reference;
        }
    }
}
