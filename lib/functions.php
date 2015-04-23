<?php

	/**
	 * Handles voting on an entity
	 *
	 * @param  integer  $guid  The entity guid being voted on
	 * @param  integer  $vote The vote
	 * @return string   A status message to be returned to the client
	 */
	function elggx_fivestar_vote($guid, $vote) {
		$result = false;
		
		// do we have an entity
		if (!empty($guid) && ($entity = get_entity($guid))) {
			// do we have a logged in user
			if ($user_guid = elgg_get_logged_in_user_guid()) {
				$vote = sanitise_int($vote, false);
				
				$annotation_options = array(
					"guid" => $entity->getGUID(),
					"type" => $entity->getType(),
					"annotation_name" => "fivestar",
					"annotation_owner_guid" => $user_guid,
					"limit" => 1
				);
				
				// already voted?
				if ($annotations = elgg_get_annotations($annotation_options)) {
					// yes
					
					// are we allowed the change/cancel our vote
					// 1 = yes
					// 0 = no
					$change_cancel = (int) elgg_get_plugin_setting("change_cancel", "elggx_fivestar");
					
					// check if we want to cancel (vote = 0)
					if (($vote == 0) && $change_cancel) {
						// fire a hook to allow other plugins to halt the action
						$params = array(
							"entity" => $entity,
							"vote" => $vote,
							"user_guid" => $user_guid
						);
						if (!elgg_trigger_plugin_hook("elggx_fivestar:cancel", "all", $params, false)) {
							// nobody stopped us, so remove the annotation
							$annotations[0]->delete();
							
							// let the user know
							$result = elgg_echo("elggx_fivestar:deleted");
						}
					} else if ($change_cancel) {
						// we want to update
						update_annotation($annotations[0]->id, "fivestar", $vote, "integer", $user_guid, ACCESS_PUBLIC);
						
						$result = elgg_echo("elggx_fivestar:updated");
					} else {
						// not allowed to update/cancel
						$result = elgg_echo("elggx_fivestar:nodups");
					}
				} elseif ($vote > 0) {
					// no, and wish to vote
					
					// fire a hook to allow other plugins to halt the action
					$params = array(
						"entity" => $entity,
						"vote" => $vote,
						"user_guid" => $user_guid
					);
					if (!elgg_trigger_plugin_hook("elggx_fivestar:vote", "all", $params, false)) {
						// nobody stopped us, so save the vote
						$entity->annotate("fivestar", $vote, ACCESS_PUBLIC, $user_guid);
					}
				} else {
					// incorrect vote
					$result = elgg_echo("elggx_fivestar:novote");
				}
			
				// update the avarage vote on the entity
				elggx_fivestar_set_rating($entity);
			}
		}
	
		return $result;
	}
	
	/**
	 * Set the current rating for an entity
	 *
	 * @param  object   $entity  The entity to set the rating on
	 * @return array    Includes the current rating and number of votes
	 */
	function elggx_fivestar_set_rating(ElggEntity $entity) {
		$result = false;
		
		if (!empty($entity) && elgg_instanceof($entity)) {
			$access = elgg_set_ignore_access(true);
		
			$rating = elggx_fivestar_get_rating($entity->getGUID());
			$entity->elggx_fivestar_rating = (float) elgg_extract("rating", $rating, 0);
		
			elgg_set_ignore_access($access);
		}
	
		return $result;
	}
	
	/**
	 * Get an the current rating for an entity
	 *
	 * @param  integer  $guid  The entity guid being voted on
	 * @return array    Includes the current rating and number of votes
	 */
	function elggx_fivestar_get_rating($guid) {
		$result = array("rating" => 0, "votes" => 0);
		
		if (!empty($guid) && ($entity = get_entity($guid))) {
		
			if ($count = $entity->countAnnotations("fivestar")) {
				$result["rating"] = $entity->getAnnotationsAvg("fivestar");
				$result["votes"] = $count;
		
				$num_stars = (int) elgg_get_plugin_setting("stars", "elggx_fivestar");
				if ($num_stars < 2) {
					$num_stars = 5;
				}
				
				$modifier = 100 / $num_stars;
				
				$result["rating"] = round($result["rating"] / $modifier, 1);
			}
		}
	
		return $result;
	}
	
	/**
	 * Inserts the fivestar widget into the current view
	 *
	 * @param  string   $returnvalue  The original html
	 * @param  array    $params  An array of parameters for the current view
	 * @param  array    $guid  The fivestar view configuration
	 * @return string   The original view or the view with the fivestar widget inserted
	 */
	function elggx_fivestar_widget($returnvalue, $params, $options) {
	
		// load library if needed
		if (!function_exists("str_get_html")) {
			elgg_load_library("simplehtmldom");
		}
		
		// do we have a guid to bind the rating to
		if (is_array($params) && isset($params["vars"]) && isset($params["vars"]["entity"])) {
			$guid = $params["vars"]["entity"]->getGUID();
		} else {
			return;
		}
	
		// different output for some contexts
		if (elgg_in_context("admin")) {
			// no extra's here
			return;
		} elseif (elgg_in_context("widgets")) {
			// 2013-06-25: for now disable extension in widgets
			return;
// 			$widget = elgg_view("elggx_fivestar/voting", array("fivestar_guid" => $guid, "min" => true));
		} else {
			$widget = elgg_view("elggx_fivestar/voting", array("fivestar_guid" => $guid));
		}
	
		// get the DOM
		$html = str_get_html($returnvalue);
		$match = false;
		
		if (!empty($html) && ($tag = elgg_extract("tag", $options))) {
			$attribute = elgg_extract("attribute", $options);
			$attribute_value = elgg_extract("attribute_value", $options);
			
			$before_html = elgg_extract("before_html", $options, "");
			$after_html = elgg_extract("after_html", $options, "");
			
			if (!empty($attribute) && !empty($attribute_value)) {
				// loop through the parsed html
				foreach ($html->find($tag) as $element) {
					
					if ($element->$attribute == $attribute_value) {
						$element->innertext .= $before_html . $widget . $after_html;
						$match = true;
						break;
					}
				}
			}
		}
	
		
		return array($match, $html);
	}
	
	/**
	 * Checks to see if the current user has already voted on the entity
	 *
	 * @param  guid   The entity guid
	 * @return bool   Returns true/false
	 */
	function elggx_fivestar_has_voted($guid) {
		$result = false;
		
		if (!empty($guid)) {
			if (($entity = get_entity($guid)) && ($user_guid = elgg_get_logged_in_user_guid())) {
				$options = array(
					"guids" => $entity->getGUID(),
					"types" => $entity->getType(),
					"annotation_name" => "fivestar",
					"annotation_owner_guid" => $user_guid,
					"count" => true
				);
				
				if ($annotation = elgg_get_annotations($options)) {
					$result = true;
				}
			}
		}
	
		return $result;
	}
	
	/**
	 * Set default settings
	 *
	 */
	function elggx_fivestar_settings() {
		// Set plugin defaults
		if (!(int) elgg_get_plugin_setting("stars", "elggx_fivestar")) {
			elgg_set_plugin_setting("stars", 5, "elggx_fivestar");
		}
		
		$change_vote = (int) elgg_get_plugin_setting("change_vote", "elggx_fivestar");
		if ($change_vote == 0) {
			elgg_set_plugin_setting("change_cancel", 0, "elggx_fivestar");
		} else {
			elgg_set_plugin_setting("change_cancel", 1, "elggx_fivestar");
		}
	}
	
	function elggx_fivestar_defaults() {
	
		$elggx_fivestar_view = array(
			"elggx_fivestar_view=object/blog, tag=div, attribute=class, attribute_value=elgg-subtext, before_html=<br />",
			"elggx_fivestar_view=object/file, tag=div, attribute=class, attribute_value=elgg-subtext, before_html=<br />",
			"elggx_fivestar_view=object/bookmarks, tag=div, attribute=class, attribute_value=elgg-subtext, before_html=<br />",
			"elggx_fivestar_view=object/page_top, tag=div, attribute=class, attribute_value=elgg-subtext, before_html=<br />",
			"elggx_fivestar_view=object/thewire, tag=div, attribute=class, attribute_value=elgg-subtext, before_html=<br />",
			"elggx_fivestar_view=group/default, tag=div, attribute=class, attribute_value=elgg-subtext, before_html=<br>",
			"elggx_fivestar_view=object/groupforumtopic, tag=div, attribute=class, attribute_value=elgg-subtext, before_html=<br />",
			"elggx_fivestar_view=icon/user/default, tag=div, attribute=class, attribute_value=elgg-avatar elgg-avatar-large, before_html=<br>",
			"elggx_fivestar_view=object/album, tag=div, attribute=class, attribute_value=elgg-subtext, before_html=<br />",
			"elggx_fivestar_view=object/image, tag=div, attribute=class, attribute_value=elgg-subtext, before_html=<br />"
		);
	
		elgg_set_plugin_setting("elggx_fivestar_view", implode("\n", $elggx_fivestar_view), "elggx_fivestar");
	}
	
	/**
	 * Is the given view configured to be extended by Elggx Fivestar
	 *
	 * @param string $view an Elgg view
	 * @return false | array the options for this view
	 */
	function elggx_fivestar_is_configured_view($view) {
		static $configured_views;
		$result = false;
		
		if (!empty($view)) {
			// load the plugin settings (once)
			if (!isset($configured_views)) {
				$configured_views = false;
				
				if ($setting = elgg_get_plugin_setting("elggx_fivestar_view", "elggx_fivestar")) {
					$lines = explode("\n", $setting);
					$temp_views = array();
					
					foreach ($lines as $line) {
						$options = array();
						$params = explode(",", $line);
						foreach ($params as $parameter) {
							preg_match("/^(\S+)=(.*)$/", trim($parameter), $match);
							//var_dump($match);
							$options[$match[1]] = $match[2];
						}
						
						if ($configured_view = elgg_extract("elggx_fivestar_view", $options)) {
							$temp_views[$configured_view] = $options;
						}
					}
					
					// store the plugin settings in the static
					if (!empty($temp_views)) {
						$configured_views = $temp_views;
					}
				}
			}
			
			// check if the view exists
			if (!empty($configured_views)) {
				$result = elgg_extract($view, $configured_views, false);
			}
		}
		
		return $result;
	}
	
	function elggx_fivestar_get_configured_stars() {
		static $result;
		
		if (!isset($result)) {
			$result = 5;
			
			if ($setting = (int) elgg_get_plugin_setting("stars", "elggx_fivestar")) {
				if ($setting > 1) {
					$result = $setting;
				}
			}
		}
		
		return $result;
	}
	
	function elggx_fivestar_cancel_change_allowed() {
		static $result;
		
		if (!isset($result)) {
			if ((int) elgg_get_plugin_setting("change_cancel", "elggx_fivestar")) {
				$result = true;
			} else {
				$result = false;
			}
		}
		
		return $result;
	}
	