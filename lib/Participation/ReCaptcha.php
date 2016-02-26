<?php

namespace Participation;

class ReCaptcha implements CaptchaInterface
{

    private $siteKey;
    private $secret;

    /**
     * string the name of the POST parameter for the captcha response
     */
    const PARAMETER_NAME = 'g-recaptcha-response';

    public function getParameterName()
    {
        return self::PARAMETER_NAME;
    }

    /**
     * @param mixed $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * @param mixed $siteKey
     */
    public function setSiteKey($siteKey)
    {
        $this->siteKey = $siteKey;
    }

    public function getWidgetMarkup()
    {
        return '<div class="g-recaptcha" data-sitekey="'.$this->siteKey.'"></div>';
    }

    public function getWidgetHeadScript()
    {
        return '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
    }

    public function isValidResponse($response)
    {
        //$this->logger->info('RPC::isValidCaptcha CHECK '.substr($captcha,0,200).' [..]');

        try {

            $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
            $encoded = '';
            $encoded .= urlencode('secret') . '=' . urlencode($this->secret) . '&';
            $encoded .= urlencode('response') . '=' . urlencode($response) . '&';
            $encoded .= urlencode('remoteip') . '=' . urlencode($_SERVER['REMOTE_ADDR']) . '&';
            // chop off last ampersand
            $encoded = substr($encoded, 0, strlen($encoded) - 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $resultText = curl_exec($ch);
            $result = json_decode($resultText);
            curl_close($ch);

            if ($result->success !== true) {

                //$this->logger->notice('RPC::isValidCaptcha FAILURE '.$resultText);
                return false;
            }

            //$this->logger->info('RPC::isValidCaptcha SUCCESS '.$resultText);

            return true;

        } catch (\Exception $exception) {

            //$this->logger->error('RPC::isValidCaptcha EXCEPTION '.$resultText.' '. $exception->getMessage());
            return false;
        }
    }
}