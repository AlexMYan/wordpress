<?php

namespace Helper;

require_once($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');

class ReCaptcha
{
    private $property;

	private $secretKey;

    /**
     * URL for reCAPTCHA siteverify API
     * @const string
     */
    const SITE_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';


    /**
     * construct
     */
    public function __construct()
    {
        $this->property = 'ReCaptcha';

    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }



	public function reCaptchaCheck($secretKey,$grecaptcha){
		if ($secretKey == null || $secretKey == "") {
			return false;
		}
		return $this->verifyResponse($secretKey,$grecaptcha);

	}

	private function verifyResponse($key,$grecaptcha){

		$query = array(
			"secret" => $key, // Ключ для сервера
			"response" => $grecaptcha, // Данные от капчи
			"remoteip" => $_SERVER['REMOTE_ADDR'] // Адрес сервера
		);

		// Создаём запрос для отправки
		$ch = curl_init();
		// Настраиваем запрос
		curl_setopt($ch, CURLOPT_URL, self::SITE_VERIFY_URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
		// отправляет и возвращает данные
		$data = json_decode(curl_exec($ch), $assoc = true);
		// Закрытие соединения
		curl_close($ch);

		// Если нет success то
		if (!$data['success']) {
			return $data;
		}

		return true;
	}
}
