<?php

class Participation_DemoController extends \Pimcore\Controller\Action\Frontend
{
    
    public function simpleAction()
    {

        if (
            !\Participation\Plugin::getConfig()->get(
                \Participation\Plugin::CONFIG_DEMOENABLED, false
            )
        ) {
            die('Participation Plugin Demo not enabled in config!');
        }

        $this->view->isFormValid = false;
        $this->view->invalidFormReason = '';

        if ($this->getParam('submit') != '') {

            $manager = \Participation\Plugin::makeManager();

            $captcha = $manager->makeCaptcha();
            $response = $this->getParam($captcha->getParameterName());

            if ($captcha->isValidResponse($response)) {

                // @todo validate/sanitize data!

                $participation = $manager->makeParticipation();
                $participation->setEmail($this->getParam('email'));
                $participation->setFirstname($this->getParam('firstname'));
                $participation->setLastname($this->getParam('lastname'));

                // this a demo - only a few fields are used!

                $participation->save();

                $confirmation = $manager->makeConfirmation();
                $confirmation->sendEmail($participation);

                $this->view->isFormValid = true;

            } else {
                $this->view->isFormValid = false;
                $this->view->invalidFormReason = 'Captcha validation failed.';
            }

        }
    }
}
