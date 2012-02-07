<?php 
$response = new Response();
//KooKoo-Adding session variables as KooKoo does not send caller data in all requests
$_SESSION['CallSid']=$_REQUEST['sid'];
$_SESSION['SmsSid']=$_REQUEST['sid'];
$_SESSION['From']=$_REQUEST['cid'];
$_SESSION['To']=$_REQUEST['called_number'];

$next = AppletInstance::getDropZoneUrl('next');
if (!empty($next))
{
	$response->addRedirect($next);    
}

$response->Respond();

