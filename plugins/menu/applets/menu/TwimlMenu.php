<?php

class TwimlMenu {
	/**
	 * For testing only. Some proxies and firewalls 
	 * don't properly pass or set the server name so 
	 * cookies may not set due to a mismatch. Use this
	 * only in testing if you're having trouble setting
	 * cookies as it will break in load-balanced
	 * server configurations
	 *
	 * @var bool
	 */
	private $use_sessions = false;
	
	protected $cookie_name;
		
	public $invalidtries;
	public $response;
	public $maxtries;
	
	public function __construct(){
		$this->cookie_name = 'invalidTries-'.AppletInstance::getInstanceId();
		$this->maxtries = AppletInstance::getValue('repeat-count', 3);
	}
	
 public function invalidTriesOver(){
 	
 	$this->invalidtries = $this->_get_invalidTries();
 	if(empty($this->invalidtries)){
 		$this->invalidtries = 1;
 	}else{
 		$this->invalidtries++;
 	}
 	
  	if($this->maxtries <0){
 		return false;
  	}
   	$this->save_invalidTries();
   	if($this->invalidtries > $this->maxtries){
  		return true;
  	}
  	return false;
 }
	
	public function respond() {
		$this->response->Respond();
	}

	private function _get_invalidTries() {
		if ($this->use_sessions) {
			$CI =& get_instance();
			$invalidtries = $CI->session->userdata($this->cookie_name);
		}
		else {
			$invalidtries = $_COOKIE[$this->cookie_name];
		}
		return $invalidtries;
	}
	
	/**
	 * Store the state for use on the next go-around
	 *
	 * @return void
	 */
	public function save_invalidTries() {
		$invalidtries = $this->invalidtries;
//		if (is_array($invalidtries)) {
//			$invalidtries = json_encode((object) $invalidtries);
//		}
		$invalidtries = (!empty($invalidtries)) ? $invalidtries : 1;
		if ($this->use_sessions) {
			$CI =& get_instance();
			$CI->session->set_userdata($this->cookie_name, $invalidtries);
		}
		else {
			set_cookie($this->cookie_name, $invalidtries, time() + (5 * 60));
		}
	}
}

?>
