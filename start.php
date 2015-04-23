<?php

	require_once(dirname(__FILE__) . "/lib/functions.php");
	require_once(dirname(__FILE__) . "/lib/hooks.php");

	elgg_register_event_handler("init", "system", "elggx_fivestar_init");

	function elggx_fivestar_init() {
	
		// register external libraries
		elgg_register_library("simplehtmldom", dirname(__FILE__) . "/vendors/simplehtmldom/simple_html_dom.php");
		
		// extend css
		elgg_extend_view("css/elgg", "css/elggx_fivestar/site");
		elgg_extend_view("js/elgg", "js/elggx_fivestar/site");
		
		// register JS library
		elgg_register_js("jquery.ui.stars", "mod/elggx_fivestar/vendors/jquery/ui.stars.min.js");
		
		// register plugin hooks
		elgg_register_plugin_hook_handler("view", "all", "elggx_fivestar_view");
		
		// Register actions
		elgg_register_action("elggx_fivestar/rate", dirname(__FILE__) . "/actions/rate.php", "logged_in");
		elgg_register_action("elggx_fivestar/settings/save", dirname(__FILE__) . "/actions/settings.php", "admin");
		elgg_register_action("elggx_fivestar/reset", dirname(__FILE__) . "/actions/reset.php", "admin");
	}
