<?php

/*
 * 	This is the form to set the plugin settings
 */

// only admins can see this page
admin_gatekeeper();

global $CONFIG;

//set the page title
$title  = elgg_echo('spam_throttle:settings');


// start form
$form = "<div style=\"margin: 15px;\">";

// preamble & explanation
$form .= elgg_echo('spam_throttle:explanation') . "<br><br>";

// globals
$form .= "<h2>" . elgg_echo('spam_throttle:settings:global') . "</h2><br>";
$form .= elgg_view('input/text', array('internalname' => 'settings[global_limit]', 'value' => get_plugin_setting('global_limit', 'spam_throttle'), 'js' => 'style="width: 50px"'));
$form .= " " . sprintf(elgg_echo('spam_throttle:helptext:limit'), elgg_echo('spam_throttle:new_content')) . "<br>";

$form .= elgg_view('input/text', array('internalname' => 'settings[global_time]', 'value' => get_plugin_setting('global_time', 'spam_throttle'), 'js' => 'style="width: 50px;"'));
$form .= " " . elgg_echo('spam_throttle:helptext:time') . "<br><br>";

// loop through all of our object subtypes

// first get a list of all of the subtypes - is there a better way than a direct query?
$subtypes = get_data("SELECT subtype FROM " . $CONFIG->dbprefix . "entity_subtypes WHERE type = 'object' AND subtype NOT IN('plugin','widget','reported_content')");

foreach($subtypes as $subtype){
	$form .= "<h2>" . sprintf(elgg_echo('spam_throttle:settings:subtype'), elgg_echo($subtype->subtype)) . "</h2><br>";
	$form .= elgg_view('input/text', array('internalname' => 'settings['.$subtype->subtype.'_limit]', 'value' => get_plugin_setting($subtype->subtype.'_limit', 'spam_throttle'), 'js' => 'style="width: 50px"'));
	$form .= " " . sprintf(elgg_echo('spam_throttle:helptext:limit'), elgg_echo($subtype->subtype)) . "<br>";

	$form .= elgg_view('input/text', array('internalname' => 'settings['.$subtype->subtype.'_time]', 'value' => get_plugin_setting($subtype->subtype.'_time', 'spam_throttle'), 'js' => 'style="width: 50px;"'));
	$form .= " " . elgg_echo('spam_throttle:helptext:time') . "<br><br>";
}

// comments
$form .= "<h2>" . elgg_echo('spam_throttle:settings:comment') . "</h2><br>";
$form .= elgg_view('input/text', array('internalname' => 'settings[annotation_generic_comment_limit]', 'value' => get_plugin_setting('annotation_generic_comment_limit', 'spam_throttle'), 'js' => 'style="width: 50px"'));
$form .= " " . sprintf(elgg_echo('spam_throttle:helptext:limit'), elgg_echo('spam_throttle:comment')) . "<br>";

$form .= elgg_view('input/text', array('internalname' => 'settings[annotation_generic_comment_time]', 'value' => get_plugin_setting('annotation_generic_comment_time', 'spam_throttle'), 'js' => 'style="width: 50px;"'));
$form .= " " . elgg_echo('spam_throttle:helptext:time') . "<br><br>";


// action to perform if threshold is broken
$form .= "<h2>" . elgg_echo('spam_throttle:consequence:title') . "</h2><br>";
$value = get_plugin_setting('consequence', 'spam_throttle');
$selectopts = array();
$selectopts['internalname'] = "settings[consequence]";
$selectopts['value'] = !empty($value) ? $value : "suspend";
$selectopts['options_values'] = array('nothing' => elgg_echo('spam_throttle:nothing'), 'suspend' => elgg_echo('spam_throttle:suspend'), 'ban' => elgg_echo('spam_throttle:ban'), 'delete' => elgg_echo('spam_throttle:delete'));
$form .= elgg_view('input/pulldown', $selectopts) . "<br>";
$form .= elgg_echo('spam_throttle:consequence:explanation');
$form .= "<ul><li><b>" . elgg_echo('spam_throttle:nothing') . "</b> - " . elgg_echo('spam_throttle:nothing:explained') . "<br></li>";
$form .= "<li><b>" . elgg_echo('spam_throttle:suspend') . "</b> - " . elgg_echo('spam_throttle:suspend:explained') . "<br></li>";
$form .= "<li><b>" . elgg_echo('spam_throttle:ban') . "</b> - " . elgg_echo('spam_throttle:ban:explained') . "<br></li>";
$form .= "<li><b>" . elgg_echo('spam_throttle:delete') . "</b> - " . elgg_echo('spam_throttle:delete:explained') . "</li></ul><br>";


// length of time of a suspension
$value = get_plugin_setting('suspensiontime', 'spam_throttle');
$form .= "<h2>" . elgg_echo('spam_throttle:suspensiontime') . "</h2><br>";
$form .= elgg_view('input/text', array('internalname' => 'settings[suspensiontime]', 'value' => !empty($value) ? $value : 24, 'js' => 'style="width: 50px"'));
$form .= " " . elgg_echo('spam_throttle:helptext:suspensiontime') . "<br><br>";

// get all site admins
$allusers = elgg_get_entities(array('types' => array('user'), 'limit' => 0));
$exempt = unserialize(get_plugin_setting('exempt', 'spam_throttle'));

if(!is_array($allusers)){
	$allusers = array();
}

$admin = array();
foreach($allusers as $user){
	if($user->isAdmin()){
		$admin[] = $user->guid;
	}
}

if(!is_array($exempt)){
	$exempt = array();
}

$exempt = array_merge($exempt, $admin);

// exemptions
$pickeropts = array(
	'internalname' => 'exempt',
	'value' => $exempt,
	'entities' => $allusers,
);
$form .= "<h2>" . elgg_echo('spam_throttle:exemptions') . "</h2><br>";
$form .= elgg_view('friends/picker', $pickeropts);
$form .= elgg_echo('spam_throttle:helptext:exemptions') . "<br><br>";

// submit button
$form .= elgg_view('input/submit', array('internalname' => "Submit", 'value' => elgg_echo("Submit")));
$form .= "</div>";

// parameters for form generation - enctype must be 'multipart/form-data' for file uploads 
$form_vars = array();
$form_vars['body'] = $form;
$form_vars['name'] = 'update_spam_throttle_settings';
$form_vars['action'] = $CONFIG->url."action/spam_throttle/settings";

// create the form
$area =  elgg_view('input/form', $form_vars);

// place the form into the elgg layout
$body = elgg_view_layout('two_column_left_sidebar', '', $area);

// display the page
page_draw($title, $body);