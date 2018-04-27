<?php

namespace bskl\LaravelMpSms;

use MesajPaneli\MesajPaneliApi;

class MpSms
{
    /**
     * @var string
     */
    private $from;

    /**
     * @var MesajPaneliApi|null
     */
    private $client;

    /**
     * MphSms constructor.
     *
     * @param   void
     * @return  self
     */
    public function __construct(string $from)
    {
        $this->from = $from ?: config('mp-sms.from');

        $this->client = new MesajPaneliApi(
            config('mp-sms.username'),
            config('mp-sms.password')
        );
    }

    /**
     * Shortcut for creating sms.
     *
     * @param   string|array $to
     * @param   string|array $content
     * @param   string       $from
     * @return  \MesajPaneli\MesajPaneliApi
     */
    public function sendSms($to, $content, $from = false)
    {
        $from = $from ?: $this->from;

        if (is_array($to)) {
            $data = [
                "msg" => $content,
                "tel" => $to,
            ];

            $message = $this->client->topluMesajGonder($from, $data);

            return $message;
        }

        if (is_array($content)) {
            foreach ($content as $sms) {
                $data[] = [
                    "tel" => $to,
                    "msg" => $sms,
                ];
            }

            $message = $this->client->parametrikMesajGonder($from, $data);

            return $message;
        }

        $this->client->parametrikMesajEkle($to, $content);

        $message = $this->client->parametrikMesajGonder($from);

        return $message;
    }

    /**
     * Get sms send report for the given reference number.
     *
     * @param   string $ref
     * @return  array  \MesajPaneli\MesajPaneliApi
     */
    public function getSmsSendReport($ref)
    {
        return $this->client->raporDetay($ref);
    }
}