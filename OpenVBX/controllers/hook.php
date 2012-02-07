<?php
/**
 * "The contents of this file are subject to the Mozilla Public License
 *  Version 1.1 (the "License"); you may not use this file except in
 *  compliance with the License. You may obtain a copy of the License at
 *  http://www.mozilla.org/MPL/
 
 *  Software distributed under the License is distributed on an "AS IS"
 *  basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 *  License for the specific language governing rights and limitations
 *  under the License.

 *  The Original Code is OpenVBX, released June 15, 2010.

 *  The Initial Developer of the Original Code is Twilio Inc.
 *  Portions created by Twilio Inc. are Copyright (C) 2010.
 *  All Rights Reserved.

 * Contributor(s):
 * Tim Lytle <tim@timlytle.net>
 * Chad Smith <chad@nospam.me>
 **/

class HookException extends Exception {}

// This controller handles unauthenticated web requests to plugins
class Hook extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$args = func_get_args();
		$hook = implode('/', $args);
		$plugins = Plugin::all();
		foreach($plugins as $plugin)
		{
			// First plugin wins
			$data['script'] = $plugin->getHookScript($hook);

			if(!empty($data['script']))
			{
				// include the script
				define("HOOK", true);
				require($data['script']);
				return;
			}
		}

		redirect('');
	}
}
?>
