<?php

	/**
	 * This method is called when the view plugin hook is triggered.
	 * If a matching view config is found then the fivestar widget is
	 * called.
	 *
	 * @param  integer  $hook The hook being called.
	 * @param  integer  $type The type of entity you"re being called on.
	 * @param  string   $return The return value.
	 * @param  array    $params An array of parameters for the current view
	 * @return string   The html
	 */
	function elggx_fivestar_view($hook, $entity_type, $returnvalue, $params) {
		$result = $returnvalue;
		
		if (!empty($params) && is_array($params)) {
			if ($view = elgg_extract("view", $params)) {
				if (!elgg_in_context("livesearch")) {
					if ($options = elggx_fivestar_is_configured_view($view)) {
						list($status, $html) = elggx_fivestar_widget($result, $params, $options);
						
						// status = 1 means we changed stuff
						if (!empty($status)) {
							$result = $html;
						}
					}
				}
			}
		}
		
		return $result;
	}