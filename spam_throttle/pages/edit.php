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
$form .= elgg_view('input/text', array('internalname' => 'global_limit', 'value' => get_plugin_setting('global_limit', 'spam_throttle'), 'js' => 'style="width: 50px"'));
$form .= " " . sprintf(elgg_echo('spam_throttle:helptext:limit'), elgg_echo('spam_throttle:new_content')) . "<br>";

$form .= elgg_view('input/text', array('internalname' => 'global_time', 'value' => get_plugin_setting('global_time', 'spam_throttle'), 'js' => 'style="width: 50px;"'));
$form .= " " . elgg_echo('spam_throttle:helptext:time') . "<br><br>";

// loop through all of our object subtypes

// first get a list of all of the subtypes - is there a better way than a direct query?
$subtypes = get_data("SELECT subtype FROM " . $CONFIG->dbprefix . "entity_subtypes WHERE type = 'object' AND subtype NOT IN('plugin','widget','reported_content')");

foreach($subtypes as $subtype){
	$form .= "<h2>" . sprintf(elgg_echo('spam_throttle:settings:subtype'), elgg_echo($subtype->subtype)) . "</h2><br>";
	$form .= elgg_view('input/text', array('internalname' => $subtype->subtype.'_limit', 'value' => get_plugin_setting($subtype->subtype.'_limit', 'spam_throttle'), 'js' => 'style="width: 50px"'));
	$form .= " " . sprintf(elgg_echo('spam_throttle:helptext:limit'), elgg_echo($subtype->subtype)) . "<br>";

	$form .= elgg_view('input/text', array('internalname' => $subtype->subtype.'_time', 'value' => get_plugin_setting($subtype->subtype.'_time', 'spam_throttle'), 'js' => 'style="width: 50px;"'));
	$form .= " " . elgg_echo('spam_throttle:helptext:time') . "<br><br>";
}

// comments
$form .= "<h2>" . elgg_echo('spam_throttle:settings:comment') . "</h2><br>";
$form .= elgg_view('input/text', array('internalname' => 'generic_comment_limit', 'value' => get_plugin_setting('generic_comment_limit', 'spam_throttle'), 'js' => 'style="width: 50px"'));
$form .= " " . sprintf(elgg_echo('spam_throttle:helptext:limit'), elgg_echo('spam_throttle:comment')) . "<br>";

$form .= elgg_view('input/text', array('internalname' => 'generic_comment_time', 'value' => get_plugin_setting('generic_comment_time', 'spam_throttle'), 'js' => 'style="width: 50px;"'));
$form .= " " . elgg_echo('spam_throttle:helptext:time') . "<br>";


// submit button
$form .= elgg_view('input/submit', array('value' => elgg_echo("Submit")));
$form .= "</div>";

//$form .= "<pre>" . print_r($subtypes,1) . "</pre>";


// parameters for form generation - enctype must be 'multipart/form-data' for file uploads 
$form_vars = array();
$form_vars['body'] = $form;
$form_vars['name'] = 'update_spam_throttle_settings';
$form_vars['action'] = $CONFIG->url . 'mod/spam_throttle/actions/spam_throttle_settings.php';

// create the form
$area =  elgg_view('input/form', $form_vars);

// place the form into the elgg layout
$body = elgg_view_layout('two_column_left_sidebar', '', $area);

// display the page
page_draw($title, $body);