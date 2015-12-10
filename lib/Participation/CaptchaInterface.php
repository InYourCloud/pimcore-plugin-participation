<?php

namespace Participation;

interface CaptchaInterface
{
    /**
     * @return string
     */
    public function getWidgetMarkup();

    /**
     * @return string
     */
    public function getWidgetHeadScript();

    /**
     * @param $response string
     * @return bool
     */
    public function isValidResponse($response);
}
