<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class JambPurchaseNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $customerName;
    public $profileId;
    public $pin;
    public $amount;
    public $reference;
    public $serviceType;
    public $transactionDate;

    /**
     * Create a new message instance.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->customerName = $data['customer_name'];
        $this->profileId = $data['profile_id'];
        $this->pin = $data['pin'];
        $this->amount = $data['amount'];
        $this->reference = $data['reference'];
        $this->serviceType = $data['service_type'];
        $this->transactionDate = $data['transaction_date'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('JAMB PIN Purchase Confirmation - ' . $this->reference)
                    ->view('emails.jamb_purchase_notification');
    }
}
