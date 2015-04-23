<?php

/**
 * Fivestar settings form
 */

?>
<script type="text/javascript">
	elgg.provide("elgg.elggx_fivestar");

	elgg.elggx_fivestar.add_form_field = function() {
		var $placeholder = $('#elggx_fivestar_counter');
		var id = $placeholder.val();
    	
		$placeholder.before("<div id='elggx-fivestar-wrapper-" + id + "' class='mbm'>" +
			"<input class='elgg-input-text' type='text' name='elggx_fivestar_views[]' id='elggx-fivestar-txt-" + id + "' value='elggx_fivestar_view=' />" +
			"<a href='#' onclick='$(\"#elggx-fivestar-wrapper-" + id + "\").remove();' class='plm'>" + elgg.echo('elggx_fivestar:settings:remove_view') + "</a>" +
			"</div>");

		id = (id - 1) + 2;
		$placeholder.val(id);
    }
</script>

<?php

$plugin = elgg_extract("entity", $vars);

// number of stars to display
$num_stars = (int) $plugin->stars;
if ($num_stars < 2) {
	$num_stars = 5;
}
echo "<div>";
echo elgg_echo("elggx_fivestar:numstars");
echo elgg_view("input/dropdown", array(
	"name" => "params[stars]",
	"options" => range(2, 10),
	"value" => $num_stars,
	"class" => "mls"
));
echo "</div>";

// can a user change their vote
$noyes_options = array(
	'0' => elgg_echo('option:no'),
	'1' => elgg_echo('option:yes')
);
echo "<div>";
echo elgg_echo("elggx_fivestar:settings:change_cancel");
echo elgg_view("input/dropdown", array(
		"name" => "params[change_vote]",
		"options_values" => $noyes_options,
		"value" => $plugin->change_vote,
		"class" => "mls"
	));
echo "</div>";

// views configuration
$title = elgg_echo("elggx_fivestar:settings:view_heading");

$form = "<div class='mbm'>";
$form .= elgg_view("output/confirmlink", array(
	"href" => "action/elggx_fivestar/reset",
	"text" => elgg_echo("elggx_fivestar:settings:defaults"),
	"confirm" => elgg_echo("elggx_fivestar:settings:defaults:confirm"
)));
$form .= "</div>";

// list the configured views
$views = $plugin->elggx_fivestar_view;
$index = 0;
if (!empty($views)) {
	$lines = explode("\n", $views);
	
	foreach ($lines as $index => $line) {
	    $options = array();
	    $parms = explode(",", $line);
	    foreach ($parms as $parameter) {
	        preg_match("/^(\S+)=(.*)$/", trim($parameter), $match);
	        $options[$match[1]] = $match[2];
	    }
	
	    $sub_title = elgg_view("output/url", array(
	    	"text" => elgg_echo("elggx_fivestar:settings:show_hide"),
	    	"href" => "#elggx-fivestar-view-" . $index,
	    	"rel" => "toggle",
	    	"class" => "float-alt"
	    ));
	    $sub_title .= $options['elggx_fivestar_view'];
	    
	    $sub_content = "<div id='elggx-fivestar-view-" . $index . "' class='hidden mbs plm'>";
	    $sub_content .= elgg_view("input/text", array(
	    	"id" => "elggx-fivestar-txt-" . $index,
	    	"name" => "elggx_fivestar_views[]",
	    	"value" => $line
	    ));
	    $sub_content .= elgg_view("output/url", array(
	    	"href" => "#",
	    	"text" => elgg_echo("elggx_fivestar:settings:remove_view"),
	    	"onclick" => "$('#elggx-fivestar-wrapper-" . $index . "').remove(); return false;"
	    ));
	    $sub_content .= "</div>";
	    
	    $form .= elgg_view_module("info", $sub_title, $sub_content, array("id" => "elggx-fivestar-wrapper-" . $index, "class" => "mbs"));
	}
}

// some administration
$form .= elgg_view("input/hidden", array("id" => "elggx_fivestar_counter", "value" => $index + 1));

$form .= "<div>";
$form .= elgg_view("output/url", array(
	"text" => elgg_echo('elggx_fivestar:settings:add_view'),
	"href" => "#",
	"onclick" => "elgg.elggx_fivestar.add_form_field(); return false;",
));
$form .= "</div>";

echo elgg_view_module("inline", $title, $form);
