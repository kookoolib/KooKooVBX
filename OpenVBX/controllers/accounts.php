<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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
 **/

class Accounts extends User_Controller {

	function __construct()
	{
		parent::__construct();
		$this->section = 'accounts';
		$this->admin_only('account settings');
		$this->template->write('title', 'Users');
		$this->load->model('vbx_device');
	}

	public function index()
	{
		$this->template->add_js('assets/j/accounts.js');

		$data = $this->init_view_data();

		$users = VBX_User::search(array('is_active' => 1));
		$data['users'] = $users;

		$groups = VBX_Group::search(array('is_active' => 1));
		if(!empty($groups))
			$data['groups'] = $groups;

		$this->respond('', 'accounts', $data);
	}

	public function group($method)
	{

		switch($method) {
			case 'get':
				return $this->get_group();
			case 'save':
				return $this->save_group();
			case 'delete':
				return $this->delete_group();
			default:
				$json = array('success' => FALSE,
							  'error' => "No such method [$method]");
				$data['json'] = $json;
				break;
		}

		return $this->respond('', 'accounts', $data);
	}

	public function group_user($method)
	{
		$group_id = $this->input->post('group_id');
		$user_id = $this->input->post('user_id');
		$success = false;
		$message = '';

		try
		{
			$group = VBX_Group::get($group_id);
			$user = VBX_User::get($user_id);
		}
		catch(Exception $e)
		{
			$error = true;
			$message = 'User or Group does not exist.';
		}

		switch($method)
		{
			case 'add':
				// TODO: don't allow if already there
				$success = $group->add_user($user_id);
				if(!$success)
				{
					$message = 'Unable to add user';
				}
				break;
			case 'delete':
				$success = $group->remove_user($user_id);
				if(!$success)
				{
					$message = 'Unable to delete group';
				}
				break;
		}

		if($this->response_type != 'json')
		{
			redirect('accounts');
		}

		$data['json'] = array('error' => !$success,
							  'message' => $message,);


		$this->respond('', 'accounts', $data);
	}

	public function user($method)
	{
		if(!$this->session->userdata('is_admin'))
			redirect('');

		switch($method)
		{
			case 'get':
				return $this->get_user();
			case 'save':
				return $this->save_user();
			case 'delete':
				return $this->delete_user();
			case 'invite':
				return $this->invite_user();
			default:
				$json = array('success' => FALSE,
							  'error' => "No such method [$method]");
				$data['json'] = $json;
				break;
		}

		return $this->respond('', 'accounts', $data);
	}

	public function appsync() {
		$this->load->library('GoogleDomain');
		$api_key = $this->input->post('email');
		$api_secret = $this->input->post('password');

		$users = array();
		$groups = array();
		$message = '';
		$error = false;

		try
		{
			$users = GoogleDomain::get_users($api_key, $api_secret);
			$groups = GoogleDomain::get_groups($api_key, $api_secret);
		}
		catch (GoogleDomainException $e)
		{
			$error = true;
			$message = $e->getMessage();
		}
		$data['json'] = array(
							  'error' => $error,
							  'message' => $message,
							  'users' => $users,
							  'groups' => $groups,
							  );
		$this->respond('', 'accounts', $data);
	}

	private function get_user()
	{
		$user_id = $this->input->post('id');

		$user = VBX_User::get($user_id);
		$devices = $this->vbx_device->get_by_user($user_id);

		$devices_data = array();

		foreach ($this->vbx_device->get_by_user($user_id) as $device)
		{
			$devices_data[] = array("id" => $device->id, "name" => $device->name, "value" => $device->value);
		}

		$data['json'] = false;

		if(!empty($user))
		{
			$data['json'] = array_merge($user->values, array("devices" => $devices_data));
		}

		return $this->respond('', 'accounts', $data);
	}

	private function save_user()
	{
		$errors = array();
		$user = false;
		$id = intval($this->input->post('id'));
		$auth_type = $this->input->post('auth_type');
		$error = false;
		$message = "Failed to save user for unknown reason.";
		$shouldGenerateNewPassword = false;
		$device_id_str = trim($this->input->post('device_id'));
		$device_number = trim($this->input->post('device_number'));
		$shouldSendWelcome = false;

		try
		{
			PhoneNumber::validatePhoneNumber($device_number);
		}
		catch(PhoneNumberException $e)
		{
			$data['json'] = array('error' => true,
								  'message' => $e->getMessage());
			return $this->respond('', 'accounts', $data);
		}

		if(!empty($auth_type))
		{
			$auth_type = $this->vbx_user->get_auth_type($auth_type);
		}

		if($id > 0)
		{
			$user = VBX_User::get($id);
		}
		else
		{
			$user = VBX_User::get(array('email' => $this->input->post('email')));
			if(!empty($user) && $user->is_active == 1)
			{
				$error = true;
				$message = 'Email address is already in use.';
			}
			elseif (!empty($user) && $user->is_active == 0)
			{
				// It's an old account that was made inactive.  By re-adding it, we're
				// assuming the user wants to re-instate the old account.
				$shouldSendWelcome = true;
			}
			else
			{
				// It's a new user
				$user = new VBX_User();
				$user->online = 9;
				$shouldSendWelcome = true;
			}
		}

		if (!$error)
		{
			$fields = array('first_name',
							'last_name',
							'email',
							'is_admin');

			foreach($fields as $field)
			{
				$user->$field = $this->input->post($field);
			}

			$user->is_active = TRUE;
			$user->auth_type = isset($auth_type->id)? $auth_type->id : 1;

			try
			{
				$user->save();
				if ($shouldSendWelcome)
					$user->send_new_user_notification();
			}
			catch(VBX_UserException $e)
			{
				$error = true;
				$message = $e->getMessage();
				error_log($message);
			}

			if (!$error)
			{
				if (strlen($device_number) > 0)
				{
					// We're adding or modifying an existing device

					if (strlen($device_id_str) > 0)
					{
						// We're updating an existing record

						$device_id = intval($device_id_str);
						$device = VBX_Device::get($device_id);

						$device->value = normalize_phone_to_E164($device_number);

						try
						{
							$device->save();
						}
						catch(VBX_DeviceException $e)
						{
							$error = true;
							$message = 'Failed to update device: ' . $e->getMessage();
						}
					}
					else
					{
						// We're creating a new device record

						$number = array(
							"name" => "Primary Device",
							"value" => normalize_phone_to_E164($device_number),
							"user_id" => $user->id,
							// sms is always enabled by default
							"sms" => 1
						);

						try
						{
							$new_device_id = $this->vbx_device->add($number);
						}
						catch(VBX_DeviceException $e)
						{
							$error = true;
							$message = "Failed to add device: " . $e->getMessage();
						}
					}
				}
				else if (strlen($device_number) == 0 && strlen($device_id_str) > 0)
				{
					// We're deleting a device
					try
					{
						$this->vbx_device->delete(intval($device_id_str), $user->id);
					}
					catch(VBX_DeviceException $e)
					{
						$error = true;
						$message = "Unable to delete device entry: " . $e->getMessage();
					}
				}
			}
		}

		if ($error)
		{
			$json = array(
				'error' => $error,
				'message' => $message
			);
		}
		else
		{
			$json = array(
				'id' => $user->id,
				'first_name' => $user->first_name,
				'last_name' => $user->last_name,
				'is_active' => $user->is_active,
				'is_admin' => $user->is_admin,
				'notification' => $user->notification,
				'auth_type' => isset($auth_type->description) ? $auth_type->description : 'openvbx',
				'email' => $user->email,
				'error' => false,
				'message' => '',
				'online' => $user->online
			);
		}

		$data['json'] = $json;

		$this->respond('', 'accounts', $data);
	}

	private function delete_user()
	{
		$id = intval($this->input->post('id'));
		$user = VBX_User::get($id);
		$user->is_active = FALSE;
		$success = true;
		$errors = array();
		try
		{
			$user->save();
			$this->vbx_group->remove_user($id);
		}
		catch(VBX_UserException $e)
		{
			$success = false;
			$errors = array($e->getMessage());
		}

		$json = compact('success', 'errors');

		$data['json'] = $json;

		$this->respond('', 'accounts', $data);
		// TODO: delete it
	}

	private function get_group()
	{
		$id = $this->input->post('id');
		$group = VBX_Group::get($id);
		$json = $group->values;

		$data['json'] = $json;
		$this->respond('', 'accounts', $data);
	}

	private function save_group()
	{
		$id = intval($this->input->post('id'));
		$name = $this->input->post('name');
		$error = false;
		$message = '';

		if($id > 0)
		{
			$group = VBX_Group::get($id);
			$group->name = $name;
		}
		else
		{
			$group = VBX_Group::get(array('name' => $name));
			if(empty($group))
			{
				$group = new VBX_Group();
			}
			if($group->is_active)
			{
				$error = true;
				$message = 'Group by that name already exists';
			}
		}

		if($group->is_active == 0) {
			$group->is_active = 1;
		}

		if(!$error)
		{
			try
			{
				$group->name = $name;
				$group->save();
			}
			catch(VBX_GroupException $e)
			{
				$error = true;
				$message = $e->getMessage();
			}
		}

		$json = array('name' => $group->name,
					  'id' => $group->id,
					  'error' => $error,
					  'message' => $message);

		$data['json'] = $json;

		$this->respond('', 'accounts', $data);
	}

	private function delete_group()
	{
		$id = $this->input->post('id');
		$json = array('message' => '',
					  'error' => false);
		try
		{
			$group = VBX_Group::get(array('id' => $id));
			$group->delete();
		}
		catch(Exception $e)
		{
			error_log($e->getMessage());
			$json['message'] = 'Unable to deactivate';
			$json['error'] = true;
		}


		$data['json'] = $json;

		$this->respond('', 'accounts', $data);
	}

}
