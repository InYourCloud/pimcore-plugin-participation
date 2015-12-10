<?php

class Participation_ConfirmationController extends \Pimcore\Controller\Action\Frontend
{
    
    public function checkAction()
    {

        // reachable via http://your.domain/plugin/Participation/index/confirm
        $code = $this->getParam('code');

        $manager = \Participation\Plugin::makeManager();
        $confirmation = $manager->makeConfirmation();

        if ($confirmation->confirmParticipationByCode($code)) {

            $this->redirect(
                \Participation\Plugin::getConfig()->get(
                    \Participation\Plugin::CONFIG_CONFIRMATION_SUCCESS_URL
                )
            );

        }

        $this->redirect(
            \Participation\Plugin::getConfig()->get(
                \Participation\Plugin::CONFIG_CONFIRMATION_FAILURE_URL
            )
        );
    }

    public function successAction()
    {

    }

    public function failureAction()
    {

    }
}
