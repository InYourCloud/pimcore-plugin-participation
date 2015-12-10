<html>
<head>
</head>
<body>
<?php if ($this->editmode): ?>
<h1>Confirmation EMail</h1>
<h2>Sample contents with available placeholders:</h2>
    Hello %Object(participationId,{"method" : "getFirstname"}); %Object(participationId,{"method" : "getFirstname"});!<br>
    <br>
    Please click on the following Link in order to confirm your participation:<br><br>
    %Text(confirmationLink);<br><br>
    Thanks!
<h2>Your email:</h2>
<?php endif;?>
<div <?php if ($this->editmode) { echo 'style="wrap:nowrap; height: 400px; width: 620px; overflow: scroll; padding: 0 20px 0 20px; border: solid 1px grey;"';} ?>>
<?=$this->wysiwyg('emailBody')?>
</div>
</body>
</html>