<?php
$sms = AppletInstance::getValue('sms');
$to = AppletInstance::getValue('sms-whom-number');
$next = AppletInstance::getDropZoneUrl('next');


$smsOptions=array('to'=>$to);
$response = new Response();
$response->addSms($sms,$smsOptions);


if(!empty($next))
{
	$response->addRedirect($next);
}

$response->Respond();