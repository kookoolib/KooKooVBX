<?php

$response = new Response(); // start a new Twiml response
//KooKoo-Modifying event parameters
//if(!empty($_REQUEST['data'])) // if we've got a transcription
if($_REQUEST['event']=="Record" || $_REQUEST['event']=="Hangup") // if we've got a transcription
{
	// add a voice message 
	OpenVBX::addVoiceMessage(
							 AppletInstance::getUserGroupPickerValue('permissions'),
							 $_SESSION['CallSid'],
							 $_SESSION['From'],
							 $_SESSION['To'], 
							 //KooKoo-Changing to KooKoo parameters
							 $_REQUEST['data'],
							 $_REQUEST['duration']
							 );		
}
else
{
	$permissions = AppletInstance::getUserGroupPickerValue('permissions'); // get the prompt that the user configured
	$isUser = $permissions instanceOf VBX_User? true : false;

	if($isUser)
	{
		$prompt = $permissions->voicemail;
	}
	else
	{
		$prompt = AppletInstance::getAudioSpeechPickerValue('prompt');
	}

	$verb = AudioSpeechPickerWidget::getVerbForValue($prompt, new Say("Please leave a message. Press the pound key when you are finished."));
	$response->append($verb);

	// add a <Record>, and use VBX's default transcription handler
	$response->addRecord(array('transcribeCallback' => site_url('/twiml/transcribe') ));
}

$response->Respond(); // send response
