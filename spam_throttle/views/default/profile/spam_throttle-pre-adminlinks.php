<?php

	/**
	 * Elgg profile icon hover over: actions
	 * 
	 * @package ElggProfile
	 * 
	 * @uses $vars['entity'] The user entity. If none specified, the current user is assumed.
	 * 
	 *  editted for extendafriend
	 *  uses the guid of each user to generate unique ids for the form popup
	 */

	if (isadminloggedin()) {
		if ($_SESSION['user']->getGUID() != $vars['entity']->getGUID() && $vars['entity']->spam_throttle_suspension > time()) {
			
			$ts = time();
			$token = generate_action_token($ts);
			
			echo elgg_view('output/confirmlink', array('text' => elgg_echo("spam_throttle:unsuspend"), 'href' => "{$vars['url']}action/spam_throttle/unsuspend?guid={$vars['entity']->guid}&__elgg_token=$token&__elgg_ts=$ts"));
		}
	}

?>