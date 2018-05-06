<?php

namespace Bskl\MpSms\Channels;

use Bskl\MpSms\Channels\Messages\MpSmsMessage;
use Bskl\MpSms\MpSms;
use Bskl\MpSms\Jobs\WriteLog;
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
        if (!$to = $notifiable->routeNotificationFor('mpsms', $notification)) {
            return;
        }

        $message = $notification->toMpSms($notifiable);

        if (is_string($message)) {
            $message = new MpSmsMessage($message);
        }

        $sms = $this->client->sendSms($to, $message->content);

        $logging = $message->logging ?: $this->client->getLogging();

        if ($logging) {
            WriteLog::dispatch($this->client, $sms)
                ->delay(now()->addMinutes(2));
        }

        return $sms;
    }
}
