<?php
include_once('TwimlMenu.php');
$response = new Response();
/* Fetch all the data to operate the menu */
$digits = isset($_REQUEST['data'])? $_REQUEST['data'] : false;
$prompt = AppletInstance::getAudioSpeechPickerValue('prompt');
$invalid_option = AppletInstance::getAudioSpeechPickerValue('invalid-option');
$repeat_count = AppletInstance::getValue('repeat-count', 3);
$next = AppletInstance::getDropZoneUrl('next');
$selected_item = false;
/* Build Menu Items */
$choices = (array) AppletInstance::getDropZoneUrl('choices[]');
$keys = (array) AppletInstance::getDropZoneValue('keys[]');
$menu_items = AppletInstance::assocKeyValueCombine($keys, $choices);

$numDigits = 1;
foreach($keys as $key)
{
	if(strlen($key) > $numDigits)
	{
		$numDigits = strlen($key);
	}
}

if($digits !== false)
{
	if(!empty($menu_items[$digits]))
	{
		$selected_item = $menu_items[$digits];
		$response->addRedirect($selected_item);
		$response->Respond();
		exit;
	}
}

$objMenuTriesOver = new TwimlMenu();
if($digits !== false){
	$menutriesover =  $objMenuTriesOver->invalidTriesOver();
   if( $menutriesover && !empty($next)){
			$response->addRedirect($next);
			$response->Respond();
			exit;	
	}
	$gather = $response->addGather(compact('numDigits'));
	if($invalid_option){
	$verb = AudioSpeechPickerWidget::getVerbForValue($invalid_option, null);
	$gather->append($verb);
	}
	$verb = AudioSpeechPickerWidget::getVerbForValue($prompt, null);
	$gather->append($verb);
	$response->Respond();
	exit;
}
$menutriesover =  $objMenuTriesOver->invalidTriesOver();
$objMenuTriesOver->invalidtries=1;
$objMenuTriesOver->save_invalidTries();
$gather = $response->addGather(compact('numDigits'));
$verb = AudioSpeechPickerWidget::getVerbForValue($prompt, null);
$gather->append($verb);
$response->Respond();
exit;


// Infinite loop

/* if($invalid_option)
{
	//	$response->addRedirect();
}
else
{
	$verb = AudioSpeechPickerWidget::getVerbForValue($invalid_option, null);
	$gather->append($verb);
	$response->Respond();
	//	$response->addRedirect();

}
 */
if($repeat_count == -1)
{
	$response->addRedirect();
	// Specified repeat count
}

