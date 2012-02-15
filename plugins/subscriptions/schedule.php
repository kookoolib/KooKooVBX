<?php
	$user = OpenVBX::getCurrentUser();
	$tenant_id = $user->values['tenant_id'];
	$ci =& get_instance();
	$queries = explode(';', file_get_contents(dirname(__FILE__) . '/db.sql'));
	foreach($queries as $query)
		if(trim($query))
			$ci->db->query($query);
	if(!empty($_POST['type'])) {
		$list = intval($_POST['list']);
		if($ci->db->query(sprintf('SELECT id FROM subscribers_lists WHERE id = %d AND tenant = %d', $list, $tenant_id))->num_rows())
			$subscribers = $ci->db->query(sprintf('SELECT value FROM subscribers WHERE list = %d', $list))->result();
		else
			$subscribers = array();
		$type = $_POST['type'];
		$callerId = normalize_phone_to_E164($_POST['callerId']);
		$time = strtotime($_POST['date'] . ' ' . $_POST['time']);
		if('sms' == $type && !empty($_POST['message'])) {
			foreach($subscribers as $subscriber)
				$ci->db->insert('outbound_queue', array(
					'tenant' => $tenant_id,
					'number' => $subscriber->value,
					'type' => $type,
					'time' => $time,
					'callerId' => $callerId,
					'data' => json_encode(array(
						'message' => $_POST['message']
					))
				));
		}
		elseif('call' == $type) {
			$flow = OpenVBX::getFlows(array('id' => $_POST['flow'], 'tenant_id' => $tenant_id));
			if($flow && count($subscribers) && $flow[0]->values['data'])
				foreach($subscribers as $subscriber)
					$ci->db->insert('outbound_queue', array(
						'tenant' => $tenant_id,
						'number' => $subscriber->value,
						'type' => $type,
						'time' => $time,
						'callerId' => $callerId,
						'data' => json_encode(array(
							'id' => $flow[0]->values['id'],
							'name' => $flow[0]->values['name']
						))
					));
		}
	}
	$lists = $ci->db->query(sprintf('SELECT id, name FROM subscribers_lists WHERE tenant = %d', $tenant_id))->result();
	$flows = OpenVBX::getFlows(array('tenant_id' => $tenant_id));
	OpenVBX::addJS('jquery-ui-1.7.3.custom.min.js');
	OpenVBX::addJS('schedule.js');
	OpenVBX::addCSS('jquery-ui-1.7.3.custom.css');
?>
<style>
	.vbx-schedule form {
		display: none;
		padding: 20px 5%;
		background: #eee;
		border-bottom: 1px solid #ccc;
	}
	.vbx-schedule h3 {
		font-size: 16px;
		font-weight: bold;
		margin-top: 0;
	}
</style>
<div class="vbx-content-main">
	<div class="vbx-content-menu vbx-content-menu-top">
		<h2 class="vbx-content-heading">Schedule Flow</h2>
		<ul class="vbx-menu-items-right">
			<li class="menu-item">
				<button id="schedule-call" class="inline-button add-button"><span>Add Call</span></button>
			</li>
			<li class="menu-item">
				<button id="schedule-sms" class="inline-button add-button"><span>Add SMS</span></button>
			</li>
		</ul>
	</div>
	<div class="vbx-table-section vbx-schedule">
		<form class="schedule-sms" method="post" action="">
			<h3>Schedule SMS</h3>
			<fieldset class="vbx-input-container">
<?php if(count($callerid_numbers)): ?>
<?php if(count($lists)): ?>
				<p>
					<label class="field-label">
						<select name="list" class="medium">
<?php foreach($lists as $list): ?>
							<option value="<?php echo $list->id; ?>"><?php echo $list->name; ?></option>
<?php endforeach; ?>
						</select>
					</label>
				</p>
				<p>
					<label class="field-label">Date<br/>
						<input type="text" name="date" class="date medium" value="<?php echo date('m/d/Y'); ?>" />
					</label>
				</p>
				<p>
					<label class="field-label">Time<br/>
						<input type="text" name="time" class="time medium" value="12:00 AM" />
					</label>
				</p>
				<p>
					<label class="field-label">Caller ID<br/>
						<select name="callerId" class="medium">
<?php foreach($callerid_numbers as $number): ?>
							<option value="<?php echo $number->phone; ?>"><?php echo $number->name; ?></option>
<?php endforeach; ?>
						</select>
					</label>
				</p>
				<p><input type="hidden" name="type" value="sms" /></p>
				<p>
					<label class="field-label">Message
						<textarea rows="20" cols="100" name="message" class="medium"></textarea>
					</label>
				</p>
				<p><button type="submit" class="submit-button"><span>Add</span></button></p>
<?php else: ?>
				<p>You do not have any lists!</p>
<?php endif; ?>
<?php else: ?>
				<p>You do not have any phone numbers!</p>
<?php endif; ?>
			</fieldset>
		</form>
		<form class="schedule-call" method="post" action="">
			<h3>Schedule Call</h3>
			<fieldset class="vbx-input-container">
<?php if(count($callerid_numbers)): ?>
<?php if(count($lists)): ?>
				<p>
					<label class="field-label">
						<select name="list" class="medium">
<?php foreach($lists as $list): ?>
							<option value="<?php echo $list->id; ?>"><?php echo $list->name; ?></option>
<?php endforeach; ?>
						</select>
					</label>
				</p>
				<p>
					<label class="field-label">Date<br/>
						<input type="text" name="date" class="date medium" value="<?php echo date('m/d/Y'); ?>" />
					</label>
				</p>
				<p>
					<label class="field-label">Time<br/>
						<input type="text" name="time" class="time medium" value="12:00 AM" />
					</label>
				</p>
<?php if(count($flows)): ?>
				<p>
					<label class="field-label">Flow<br/>
						<select name="flow" class="medium">
<?php foreach($flows as $flow): ?>
							<option value="<?php echo $flow->values['id']; ?>"><?php echo $flow->values['name']; ?></option>
<?php endforeach; ?>
						</select>
					</label>
				</p>
				<p>
					<label class="field-label">Caller ID<br/>
						<select name="callerId" class="medium">
<?php foreach($callerid_numbers as $number): ?>
							<option value="<?php echo $number->phone; ?>"><?php echo $number->name; ?></option>
<?php endforeach; ?>
						</select>
					</label>
				</p>
				<p><input type="hidden" name="type" value="call" /></p>
				<p><button type="submit" class="submit-button"><span>Add</span></button></p>
<?php else: ?>
				<p>You do not have any flows!</p>
<?php endif; ?>
<?php else: ?>
				<p>You do not have any lists!</p>
<?php endif; ?>
<?php else: ?>
				<p>You do not have any phone numbers!</p>
<?php endif; ?>
			</fieldset>
		</form>
	</div>
</div>
