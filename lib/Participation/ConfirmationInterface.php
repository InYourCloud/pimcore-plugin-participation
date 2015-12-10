<?php

namespace Participation;

interface ConfirmationInterface
{

    /**
     * @return string
     */
    public function createCode();

    /**
     * @param $code string
     * @return string
     */
    public function createConfirmationLink($code);

    /**
     * @param AbstractObject $participation
     * @throws \Exception
     */
    public function sendEmail($participation);

}
