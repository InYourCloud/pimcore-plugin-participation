<html>
<head>
    <?php

    $manager = \Participation\Plugin::makeManager();
    $captcha = $manager->makeCaptcha();

    // pass in additional recaptcha options via a key-value array:
    // ?>
    <?= $captcha->getWidgetHeadScript(array('hl' => 'de')) ?>
</head>
<body>
<?php if (!$this->isFormValid): ?>
<form action="?" method="POST">
    <div style="border: solid 1px red;"><?= $this->invalidFormReason ?></div>
    <input type="email" name="email" placeholder="your@email.address"><br>
    <input type="firstname" name="firstname" placeholder="Max"><br>
    <input type="lastname" name="lastname" placeholder="Mustermann"><br>
    <?= $captcha->getWidgetMarkup() ?>
    <br/>
    <input type="submit" name="submit" value="Submit">
</form>
<?php else: ?>
    <h2>OK!</h2>
<?php endif; ?>
</body>
</html>
