<?php

include_once 'lib/functions.php';

function spam_throttle_init(){
	global $CONFIG;
	
	register_elgg_event_handler('create', 'object', 'spam_throttle_check');
	register_elgg_event_handler('create', 'annotation', 'spam_throttle_check');
}

register_elgg_event_handler('init', 'system', 'spam_throttle_init');