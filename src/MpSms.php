<?php

namespace Bskl\MpSms;

use Bskl\MpSms\MesajPaneli\MesajPaneliApi;
use Illuminate\Support\Facades\Log;

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
     * @var bool
     */
    private $logging;

    /**
     * MphSms constructor.
     *
     * @param   void
     *
     * @return self
     */
    public function __construct()
    {
        $this->from = config('mp-sms.from');

        $this->client = new MesajPaneliApi(
            config('mp-sms.username'),
            config('mp-sms.password')
        );

        $this->logging = config('mp-sms.logging');
    }

    /**
     * Shortcut for creating sms.
     *
     * @param string|array $to
     * @param string|array $content
     * @param string       $from
     *
     * @return \MesajPaneli\MesajPaneliApi
     */
    public function sendSms($to, $content, $from = null)
    {
        $from = $from ?: $this->from;

        if (is_array($to)) {
            $data = [
                'msg' => trim($content),
                'tel' => $to,
            ];

            return $this->client->topluMesajGonder($from, $data);
        }

        if (is_array($content)) {
            foreach ($content as $sms) {
                $data[] = [
                    'tel' => $to,
                    'msg' => trim($sms),
                ];
            }

            return $this->client->parametrikMesajGonder($from, $data);
        }

        $this->client->parametrikMesajEkle($to, trim($content));

        return $this->client->parametrikMesajGonder($from);
    }

    /**
     * Get sms send report for the given reference number.
     *
     * @param string $ref
     *
     * @return array \MesajPaneli\MesajPaneliApi
     */
    public function getSmsSendReport($ref)
    {
        return $this->client->raporDetay($ref);
    }

    /**
     * Write sms message to the log.
     *
     * @param bool  $logging
     * @param array $sms
     *
     * @return void
     */
    public function writeLog($sms)
    {
        $log = array_merge($sms, [
            'mp_log' => $this->getSmsSendReport($sms['ref']),
        ]);

        Log::channel('mpsms')->info(
            implode(',', array_flatten($log))
        );
    }

    /**
     * Returns the logging.
     *
     * @param   void
     *
     * @return bool
     */
    public function getLogging()
    {
        return $this->logging;
    }

    /**
     * Dynamic client method call.
     *
     * @param string $method
     * @param array  $args
     *
     * @return SmsApi
     */
    public function __call($method, array $args = [])
    {
        if (method_exists($this->client, $method)) {
            return call_user_func_array([
                $this->client,
                $method,
            ], $args);
        }

        throw new \BadMethodCallException("Invalid method $method.");
    }
}
