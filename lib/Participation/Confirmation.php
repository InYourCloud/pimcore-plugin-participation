<?php

namespace Participation;

use Pimcore\Model\Element\Note;
use Pimcore\Model\Object\Participation;
use Pimcore\View\Helper\Url as UrlHelper;
use Pimcore\Document;
use Pimcore\Model\Document as DocumentModel;
use Pimcore\Model\Document\Email as EmailDocument;

Use RandomLib\Factory as RandomLibFactory;

class Confirmation implements ConfirmationInterface
{

    private $confirmationCodeLength = 20;
    private $confirmationCodeCharacters = 'abcdefghijkmnprstuvwxyz23456789';

    public function createCode()
    {
        $factory = new RandomLibFactory;
        $generator = $factory->getLowStrengthGenerator();

        $randomString = $generator->generateString(
            $this->confirmationCodeLength,
            $this->confirmationCodeCharacters
        );
        return $randomString;
    }

    /**
     * @param string $code
     * @return string
     * @throws \Exception
     */
    public function createConfirmationLink($code)
    {

        $protocolName = $_SERVER['REQUEST_SCHEME'];
        $serverName = $_SERVER['SERVER_NAME'];

        $urlHelper = new UrlHelper;
        $confirmationLink = $urlHelper->url(
            array("code" => $code),
            Plugin::STATICROUTE_CONFIRMATIONCHECK_NAME
        );
        return $protocolName . '://' . $serverName . $confirmationLink;
    }

    /**
     * @param AbstractObject $participation
     * @throws \Exception
     */
    public function sendEmail($participation)
    {
        $email = $participation->getEmail();
        $emailDomain = trim(strtolower(preg_replace('/^[^@]+@/', '', $email)));
        $participation->setEmailDomain($emailDomain);
        $participation->save();

        $confirmationLink = $this->createConfirmationLink(
            $participation->getConfirmationCode()
        );

        $parameters = array(
            'confirmationLink' => $confirmationLink,
            'participationId' => $participation->getId()
        );

        $emailDocumentPath = Plugin::getConfig()->get('emailDocumentPath');
        $emailDocument = DocumentModel::getByPath($emailDocumentPath);

        if (!$emailDocument instanceof EmailDocument) {
            throw new \Exception(
                "Error: emailDocumentPath [$emailDocumentPath] "
                . "is not a valid email document."
            );
        }

        $mail = new \Pimcore\Mail();
        $mail->addTo($email);
        $mail->setDocument(
            $emailDocumentPath
        );
        $mail->setParams($parameters);
        $mail->send();

        $note = new Note();
        $note->setElement($participation);
        $note->setDate(time());
        $note->setType("confirmation");
        $note->setTitle("Email sent");
        $note->addData("email", "text", $email);
        $note->setUser(0);
        $note->save();
    }

    /**
     * @param $code string
     * @return bool
     */
    public function confirmParticipationByCode($code)
    {
        $participation = Participation::getByConfirmationCode($code, 1);
        if (is_object($participation)) {

            $participation->SetIpConfirmed($_SERVER['REMOTE_ADDR']);
            $participation->setConfirmed(true);
            $participation->save();

            $note = new Note();
            $note->setElement($participation);
            $note->setDate(time());
            $note->setType("confirmation");
            $note->setTitle("Code confirmed");
            $note->addData("code", "text", $code);
            $note->setUser(0);
            $note->save();

            return true;
        }

        return false;
    }
}