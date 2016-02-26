<?php

namespace Participation;

use Pimcore\API\Plugin\Exception;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\Folder;
use Pimcore\Model\Object\Participation;
use Pimcore\File;

class Manager implements ManagerInterface
{

    public function makeConfirmation()
    {
        $confirmation = new Confirmation();
        return $confirmation;
    }

    public function makeCaptcha()
    {
        $config = Plugin::getConfig();

        $captcha = new ReCaptcha();
        $captcha->setSiteKey($config->recaptcha->get('sitekey', 'SET-SITEKEY-IN-CONFIG'));
        $captcha->setSecret($config->recaptcha->get('secret', 'SET-SECRET-IN-CONFIG'));

        return $captcha;
    }

    /**
     * @return AbstractObject|\Pimcore\Model\Object\Participation
     * @throws \Exception
     */
    public function makeParticipation()
    {
        $objectFolderPath = Plugin::getConfig()->get(Plugin::CONFIG_OBJECTFOLDERPATH);
        $objectFolder = AbstractObject::getByPath($objectFolderPath);

        if (!$objectFolder instanceof Folder) {
            throw new \Exception(
                "Error: objectFolderPath [$objectFolderPath] "
                . "is not a valid object folder."
            );
        }

        // create basic object stuff
        $key = $this->createParticipationKey();

        $participation = new Participation();
        $participation->setKey($key);
        $participation->setParent($objectFolder);
        $participation->setPublished(true);
        $participation->setCreationDate(time());
        $participation->SetIpCreated($_SERVER['REMOTE_ADDR']);

        $confirmation = $this->makeConfirmation();

        $participation->setConfirmationCode(
            $confirmation->createCode()
        );

        return $participation;
    }

    /**
     * @return string
     */
    private function createParticipationKey()
    {
        return File::getValidFilename(
            "p-" . time() . '-' . rand(10000, 99999)
        );
    }
}