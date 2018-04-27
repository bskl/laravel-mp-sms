<?php

namespace bskl\LaravelMpSms\Notifications;

use MesajPaneli\MesajPaneliApi;
use Illuminate\Notifications\Notification;

class MpSmsChannel
{
    /**
     * @var MesajPaneliApi
     */
    private $client;

    /**
     * OvhSmsChannel constructor.
     *
     * @param   OvhSms $client
     * @return  self
     */
    public function __construct(MesajPaneliApi $client)
    {
        $this->client = $client;
    }

    /**
     * Send the given notification with sms.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $to = $notifiable->routeNotificationFor('mpsms'))
        {
            return;
        }

        $message = $notification->toSms($notifiable);

        try {
            $sms = $this->client->parametrikMesajEkle($to, $message->content);

            $sms->parametrikMesajGonder( 'Makbuz Oluşturuldu' );
        }
        catch ( Exception $e ) {
            echo $e->getMessage();
        }

    }
}