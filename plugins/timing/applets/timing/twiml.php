<?php
$response = new Response();
$now = date_create('now');
$today = date_format($now, 'w') - 1;
$response->addRedirect(AppletInstance::getDropZoneUrl(
  ($from = AppletInstance::getValue("range_{$today}_from"))
  && ($to = AppletInstance::getValue("range_{$today}_to"))
  && date_create($from) <= $now && $now < date_create($to)
  ? 'open'
  : 'closed'
));
$response->Respond();
