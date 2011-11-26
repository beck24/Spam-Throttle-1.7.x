<?php

/**
* ban user if sending too many messages
*
* @param string $event
* @param string $object_type
* @param ElggObject $object
* @return boolean
*/
function spam_throttle_check($event, $object_type, $object) {
	
	// release exempt users
	if(isadminloggedin()){
		return;
	}
	
	$exempt = unserialize(get_plugin_setting('exempt', 'spam_throttle'));
	if(is_array($exempt) && in_array(get_loggedin_userid(), $exempt)){
		return;
	}
	
	// reported content doesn't count (also this prevents an infinite loop...)
	if($object->getSubtype() == 'reported_content'){
		return;
	}
	
	// delete the content and warn them if they are on a suspension
	if(get_loggedin_user()->spam_throttle_suspension > time()){
		$timeleft = get_loggedin_user()->spam_throttle_suspension - time();
		$hours = ($timeleft - ($timeleft % 3600))/3600;
		$minutes = round(($timeleft % 3600)/60);
		register_error(sprintf(elgg_echo('spam_throttle:suspended'), $hours, $minutes));
		return FALSE;
	}
	
	// They've made it this far, time to check if they've exceeded limits or not
	
	// first check for global setting
	$globallimit = 5;//get_plugin_setting('global_limit', 'spam_throttle');
	$globaltime = 5;//get_plugin_setting('global_time', 'spam_throttle');
	
	if(is_numeric($globallimit) && $globallimit > 0 && is_numeric($globaltime) && $globaltime > 0){
		
		// because 2 are created initially
		if($object->getSubtype() == 'messages'){
			$globallimit++;
		}
		
		// we have globals set, lets give it a test
		$params = array(
			'type' => 'object',
			'created_time_lower' => time() - ($globaltime * 60),
			'owner_guids' => array(get_loggedin_userid()),
			'count' => TRUE,
		);
		
		$entitycount = elgg_get_entities($params);
		$commentcount = count_annotations(0, "", "", "generic_comment", "", "", get_loggedin_userid(), $params['created_time_lower'], 0);
		
		$activitytotal = $entitycount + $commentcount;
		
		if($activitytotal > $globallimit){
			spam_throttle_limit_exceeded($globaltime, $activitytotal, "Activity");
			
			// not returning false in case of false positive
			return;
		}
	}
	
	// if we're still going now we haven't exceeded globals, check for individual types
}


function spam_throttle_limit_exceeded($time, $created, $type){
	$action = "suspend"; //get_plugin_setting('action', 'spam_throttle');
	
	// four possibilities here, report & do nothing, report & suspend posting (but they can still log in - read only), report & ban, delete
	// deleting will also delete the report, so save the logic and report everything
	$report = new ElggObject;
	$report->subtype = "reported_content";
	$report->owner_guid = get_loggedin_userid();
	$report->title = elgg_echo('spam_throttle');
	$report->address = get_loggedin_user()->getURL();
	$report->description = sprintf(elgg_echo('spam_throttle:reported'), $type, $created, $time);
	$report->access_id = ACCESS_PRIVATE;
	$report->save();

	if($action == "suspend"){
		$user = get_loggedin_user();
		$suspensiontime = 24; //get_plugin_setting('suspensiontime', 'spam_throttle');
		$user->spam_throttle_suspension = time() + 60*60*$suspensiontime;
		register_error(sprintf(elgg_echo('spam_throttle:suspended'), $suspensiontime, '0'));	
	}
	
	if($action == "ban"){
		ban_user(get_loggedin_userid(), elgg_echo('spam_throttle:banned'));
		register_error(elgg_echo('spam_throttle:banned'));
	}
	
	if($action == "delete"){
		$user = get_loggedin_user();
		$user->delete();
		register_error(elgg_echo('spam_throttle:deleted'));
	}
}