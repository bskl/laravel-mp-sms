<?php

namespace Bskl\MpSms\Notifications;

use Bskl\MpSms\MpSms;
use Illuminate\Notifications\Notification;

class MpSmsChannel
{
    /**
     * @var MpSms
     */
    private $client;

    /**
     * MpSmsChannel constructor.
     *
     * @param MpSms $client
     *
     * @return self
     */
    public function __construct(MpSms $client)
    {
        $this->client = $client;
    }

    /**
     * Send the given notification with sms.
     *
     * @param mixed                                  $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (!$to = $notifiable->routeNotificationFor('mpsms')) {
            return;
        }

        $message = $notification->toMpSms($notifiable);

        try {
            $sms = $this->client->sendSms($to, $message->content);
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        $this->client->writeLog($message->logging, $sms);

        return $sms;
    }
}
