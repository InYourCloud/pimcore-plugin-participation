<?php

namespace Participation;

use Pimcore\API\Plugin as PluginLib;
use Pimcore\Document;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\Folder;
use Pimcore\Model\Staticroute;
use Pimcore\Model\Document\DocType;
use Pimcore\Model\Document\DocType\Listing as DocTypeListing;
use Pimcore\Model\Document\Page;
use Pimcore\Model\Document\Email;

class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface
{
    const SAMPLE_CONFIG_XML = "/Participation/participation.xml";
    const CONFIG_XML = '/var/config/participation.xml';
    const CONFIG_OBJECTFOLDERPATH = "objectFolderPath";
    const CONFIG_OBJECTFOLDERPATH_DEFAULT = "/participation";
    const CONFIG_DEMOENABLED = 'demoEnabled';
    const CONFIG_CONFIRMATION_SUCCESS_URL = 'confirmationSuccessUrl';
    const CONFIG_CONFIRMATION_FAILURE_URL = 'confirmationFailureUrl';
    const CONFIG_MANAGERCLASS = 'managerClass';

    const STATICROUTE_CONFIRMATIONCHECK_NAME = "participationConfirmationCheck";

    // email related constants
    const DOCTYPE_EMAIL_PARTICIPATION_CONFIRMATION_NAME =
        'Participation Confirmation';

    const DOCUMENT_EMAIL_CONFIRMATION_PATH = '/participation-confirmation';

    const EMAIL_CONFIRMATION_SUBJECT_DEFAULT =
        'Hello Participant  %Object(participationId,{"method" : "getFirstname"});';

    // demo page default path/key
    const DOCUMENT_PAGE_DEMOSIMPLE_PATH = "/participation-demo-simple";
    const CLASS_PARTICIPATION_NAME = "Participation";

    /**
     * @var \Zend_Config
     */
    private static $config = null;

    /**
     * Initialize Plugin
     *
     * Sets up Tideways, watchers, apiKey, various config options
     */
    public function init()
    {
        parent::init();

        if (!self::isInstalled()) {
            return;
        }

        $config = new \Zend_Config_Xml(self::getConfigName());
        self::$config = $config->participation;
    }

    /**
     * Install plugin
     *
     * @return string install success|failure message
     */
    public static function install()
    {
        $installError = 'unknown';

        try {

            self::createClasses();
            self::createObjects();
            self::createDocuments();
            self::createDocumentTypes();
            self::createEmailDocuments();
            self::createStaticRoutes();
            self::createConfigFile();

        } catch (\Exception $exception) {

            $installError = $exception->getMessage();
        }

        if (self::isInstalled()) {
            return "Successfully installed.";
        } else {
            return "Error: $installError";
        }
    }

    /**
     * Determine plugin install state
     *
     * @return bool true if plugin is installed (option "installed" is "1" in config file)
     */
    public static function isInstalled()
    {
        if (!file_exists(self::getConfigName())) {
            return false;
        }

        $config = new \Zend_Config_Xml(self::getConfigName());
        if ($config->participation->installed != 1) {
            return false;
        }
        return true;
    }

    /**
     * Return config file name
     *
     * @return string xml config filename
     */
    private static function getConfigName()
    {
        return PIMCORE_WEBSITE_PATH . self::CONFIG_XML;
    }

    /**
     * Returns the config
     *
     * @return \Zend_Config
     */
    public static function getConfig()
    {
        return self::$config;
    }

    private static function createClasses()
    {
        self::createClass(
            self::CLASS_PARTICIPATION_NAME,
            PIMCORE_PLUGINS_PATH . '/Participation/install/class_source/class_Participation_export.json'
        );
    }

    private static function createObjects()
    {
        try {
            $objectFolder = AbstractObject::getByPath(
                Plugin::CONFIG_OBJECTFOLDERPATH_DEFAULT
            );
            if (!is_object($objectFolder)) {
                $objectFolder = new Folder();
                $objectFolder->setKey(
                    basename(Plugin::CONFIG_OBJECTFOLDERPATH_DEFAULT)
                );
                $objectFolder->setParent(
                    AbstractObject::getByPath(
                        dirname(Plugin::CONFIG_OBJECTFOLDERPATH_DEFAULT)
                    )
                );
                $objectFolder->save();
            }

            if (!$objectFolder instanceof Folder) {

                throw new \Exception(
                    'Can not use object path ['
                    . Plugin::CONFIG_OBJECTFOLDERPATH_DEFAULT
                    . '] as default participation folder.'
                );
            }
        } catch (\Exception $exception) {

            throw new \Exception(
                'Unable to create/use participation folder ['
                . Plugin::CONFIG_OBJECTFOLDERPATH_DEFAULT
                . ']: ' . $exception->getMessage()
            );
        }
    }

    private static function createStaticRoutes()
    {
        // create/update the static route for the confirmation:

        try {

            $route = Staticroute::getByName(self::STATICROUTE_CONFIRMATIONCHECK_NAME);

            if (!is_object($route)) {
                $route = new Staticroute();
            }

            $route->setValues(
                array(
                    "name" => self::STATICROUTE_CONFIRMATIONCHECK_NAME,
                    "pattern" => "@/confirm/(.*)@",
                    "reverse" => "/confirm/%code",
                    "variables" => 'code',
                    "module" => self::CLASS_PARTICIPATION_NAME,
                    "controller" => "confirmation",
                    "action" => "check"
                )
            );
            $route->save();

        } catch (\Exception $exception) {

            throw new \Exception(
                'Unable to create static route ['
                . Plugin::STATICROUTE_CONFIRMATIONCHECK_NAME
                . ']: ' . $exception->getMessage()
            );
        }
    }


    private function createDocuments()
    {
        try {

            $page = Page::getByPath(self::DOCUMENT_PAGE_DEMOSIMPLE_PATH);
            if (!is_object($page)) {
                $page = new Page();
                $page->setParent(Page::getByPath('/'));
                $page->setKey(basename(self::DOCUMENT_PAGE_DEMOSIMPLE_PATH));
                $page->setModule(self::CLASS_PARTICIPATION_NAME);
                $page->setController('Demo');
                $page->setAction('simple');
                $page->save();
            }
        } catch (\Exception $exception) {

            throw new \Exception(
                'Unable to create simple demo page ['
                . Plugin::DOCUMENT_PAGE_DEMOSIMPLE_PATH
                . ']: ' . $exception->getMessage()
            );
        }

    }

    private function createEmailDocuments()
    {
        try {

            $email = Email::getByPath(self::DOCUMENT_EMAIL_CONFIRMATION_PATH);
            if (!is_object($email)) {
                $email = new Email();
                $email->setParent(
                    Page::getByPath(dirname(self::DOCUMENT_EMAIL_CONFIRMATION_PATH))
                );
                $email->setKey(basename(self::DOCUMENT_EMAIL_CONFIRMATION_PATH));
                $email->setModule(self::CLASS_PARTICIPATION_NAME);
                $email->setController('Email');
                $email->setAction('confirmation');
                $email->setSubject(self::EMAIL_CONFIRMATION_SUBJECT_DEFAULT);
                $email->save();
            }

        } catch (\Exception $exception) {

            throw new \Exception(
                'Unable to create email document page ['
                . Plugin::DOCUMENT_EMAIL_CONFIRMATION_PATH
                . ']: ' . $exception->getMessage()
            );
        }
    }

    /**
     * @throws \Exception
     */
    private function createDocumentTypes()
    {
        try {
            $docType = self::getDocumentTypeByName(
                self::DOCTYPE_EMAIL_PARTICIPATION_CONFIRMATION_NAME
            );
            $docType->type = 'email';
            $docType->action = 'confirmation';
            $docType->controller = 'Email';
            $docType->module = self::CLASS_PARTICIPATION_NAME;
            $docType->save();

        } catch (\Exception $exception) {

            throw new \Exception(
                'Unable to create email DocumentType ['
                . Plugin::DOCTYPE_EMAIL_PARTICIPATION_CONFIRMATION_NAME
                . ']: ' . $exception->getMessage()
            );
        }
    }

    /**
     * @throws \Exception
     */
    private function createConfigFile()
    {
        try {

            if (!file_exists(self::getConfigName())) {

                $defaultConfig = new \Zend_Config_Xml(PIMCORE_PLUGINS_PATH . self::SAMPLE_CONFIG_XML);
                $configWriter = new \Zend_Config_Writer_Xml();
                $configWriter->setConfig($defaultConfig);
                $configWriter->write(self::getConfigName());
            }

            $config = new \Zend_Config_Xml(
                self::getConfigName(),
                null,
                array('allowModifications' => true)
            );

            $config->participation->installed = 1;

            $config->participation->{self::CONFIG_OBJECTFOLDERPATH} =
                self::CONFIG_OBJECTFOLDERPATH_DEFAULT;

            $configWriter = new \Zend_Config_Writer_Xml();
            $configWriter->setConfig($config);
            $configWriter->write(self::getConfigName());

        } catch (\Exception $exception) {

            throw new \Exception(
                'Unable to create config file ['
                . self::getConfigName()
                . ']: ' . $exception->getMessage()
            );
        }
    }

    /**
     * @param $classname
     * @param $filepath
     * @throws \Exception
     */
    private static function createClass($classname, $filepath)
    {

        $class = \Pimcore\Model\Object\ClassDefinition::getByName($classname);

        if (!$class) {
            $class = new \Pimcore\Model\Object\ClassDefinition();
            $class->setName($classname);
        }

        $json = file_get_contents($filepath);
        $success = \Pimcore\Model\Object\ClassDefinition\Service::importClassDefinitionFromJson($class, $json);

        if (!$success) {
            throw new \Exception("Could not import $classname Class.");
        }
    }

    /**
     * @param $name
     * @return DocType
     */
    private static function getDocumentTypeByName($name)
    {
        $doctypeListing = new DocTypeListing();
        $doctypes = $doctypeListing->getDocTypes();

        /** @var DocType $docType */
        foreach ($doctypes as $docType) {

            if ($docType->getName() == $name) {
                return $docType;
            }
        }

        $docType = new DocType();
        $docType->name = $name;

        return $docType;
    }

    /**
     * Uninstall plugin
     *
     * Sets config file parameter "installed" to 0 (if config file exists)
     *
     * @return string uninstall success|failure message
     */
    public static function uninstall()
    {
        if (file_exists(self::getConfigName())) {

            $config = new \Zend_Config_Xml(self::getConfigName(), null, array('allowModifications' => true));
            $config->participation->installed = 0;

            $configWriter = new \Zend_Config_Writer_Xml();
            $configWriter->setConfig($config);
            $configWriter->write(self::getConfigName());
        }

        if (!self::isInstalled()) {
            return "Successfully uninstalled.";
        } else {
            return "Could not be uninstalled";
        }
    }

    /**
     * @param $class
     * @return ManagerInterface
     */
    public static function makeManager($class=null)
    {
        $managerClass = $class ? $class : self::$config->get(self::CONFIG_MANAGERCLASS);

        $manager = new $managerClass;
        return $manager;
    }
}
