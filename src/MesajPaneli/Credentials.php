<?php

namespace Bskl\MpSms\MesajPaneli;

use \Exception as AuthenticationException;

class Credentials
{
    private $username = '';
    private $password = '';
    private $endpoint = 'api.mesajpaneli.com/json_api';

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->validate();
    }

    private function validate()
    {
        if (!$this->username || !$this->password) {
            throw new AuthenticationException('Kullanıcı adı ve şifrenizi config.json dosyasında kontrol ediniz.');
        }

        $this->endpoint = (strpos($this->endpoint, 'http://') === 0) ? 'http://'.$this->endpoint : $this->endpoint;
    }

    public function getAsArray()
    {
        $this->validate();

        return [
            'user' => [
                'name' => $this->username,
                'pass' => $this->password,
            ],
        ];
    }
}
