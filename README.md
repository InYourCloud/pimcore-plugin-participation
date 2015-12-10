Participation Pimcore Plugin
================================================

Developer info: [Pimcore at basilicom](http://basilicom.de/en/pimcore)

## Synopsis

This Pimcore http://www.pimcore.org plugin simplifies building a site
with participations, e.g. raffles.

## Method of Operation

A user visiting the `/participation-demo-simple` page/url gets a form (with a captcha).
Upon completing the captcha, filling out the form and submitting it, a new `Participation`
object (key is constructed using `time()` and a random value) is added to the 
`/participation` object folder and an email `/participation-confirmation`  is send to 
the user. If the User clicks on the confirmation link in the received email, his 
`Participation` object is retrieved by looking up the confirmation code and setting the
objects `isConfirmed` property to `true`. Notes are created the the objects in order to
document the individual steps. 

The users IP is recorded during object creation / confirmation. Prior to sending the 
email, the `participation` object is updated with an extra normalized domain part of 
the users email address. These properties can later be used for additional/optional
fraud detection/prevention.

## Motivation

Implementing raffles with participations is a recurring tedious task - prone to making 
many errors. This plugin tries to use as much "Best Practices" and standard Pimcore
features as possible to simplify future implementations.

## Installation

Add "basilicom-pimcore-plugin/participation" as a requirement to the
composer.json in the toplevel directory of your Pimcore installation.

Example:

    {
        "require": {
            "basilicom-pimcore-plugin/participation": "~1.0"
        }
    }
    
Installing the plugin via the Pimcore Extension Manager performs the following steps:

* an object class `Participation` is created
* an object folder `/participation` is created
* a predefined email document type `Participation Confirmation` is created 
* a sample email document `/participation-confirmation` is created
* a demo document `/participation-demo-simple` is created
* a static route `participationConfirmationCheck` is created for `@/confirm/(.*)@`

Press the "Configure" button of the Participation plugin from within the Extension 
Manager and set the config file properties.

*Hint:* Set `demoEnabled` to `1` if you want to test the `/participation-demo-simple`. 

You need to apply for / configure a Google Recaptcha widget integration and
configure `sitekey` and `secret` in the plugin configuration file. 

Refer to https://developers.google.com/recaptcha/intro for info on Google Recaptcha. 

## Customization

The `Participation` object class can be modified as long as the `email`  field and 
the fields of the *Meta* section are kept.

You can change the static route `participationConfirmationCheck` as long as you keep
the name and the `code` parameter intact.

Change the target folder for storing the Participations by altering the `objectFolderPath`
configuration option.

Change the email document used by altering the `emailDocumentPath` configuration option.

Provide custom participation confirmation success/failure pages by changing the 
configuration options `confirmationSuccessUrl` and `confirmationFailureUrl`.

For further customization, extend the *Manager* class and (optionally) one or more of
the *Confirmation* and *ReCaptcha* classes or implement the corresponding interfaces.
You need to register your new *Manager* class by specifying the full class name in the
plugin configuration file.

## API Reference

The following interfaces are provided:
 
* `\Participation\ManagerInterface`
* `\Participation\ConfirmationInterface`
* `\Participation\CaptchaInterface`

## Tests

* none

## Todo

* Add an Ajax-based form demo.
* Add re-usable AreaBricks
* Implement the possibility to use multiple/different participations in one pimcore installation
* Implement IP-based throtteling (via leaky bucket)
* Implement email-domain-based throtteling (via leaky bucket)
* Add Application Log logging 

## Contributors

* Susanna Huhtanen <susanna.huhtanen@basilicom.de>
* Christoph Luehr <christoph.luehr@basilicom.de>

## License

* BSD-3-Clause
