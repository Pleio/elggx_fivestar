<?php



elgg_load_js("jquery.ui.stars");

$guid = elgg_extract("fivestar_guid", $vars);
if (empty($guid) && ($entity = elgg_extract("entity", $vars))) {
	$guid = $entity->getGUID();
}

// we must have a guid to assing the vote to
if (!$guid) {
	return;
}

$rating = elggx_fivestar_get_rating($guid);
$stars = elggx_fivestar_get_configured_stars();

$pps = 100 / $stars;

$checked = '';

$disabled = '';
if (!elgg_is_logged_in()) {
	$disabled = 'disabled="disabled"';
}

if (!elggx_fivestar_cancel_change_allowed() && elggx_fivestar_has_voted($guid)) {
	$disabled = 'disabled="disabled"';
}

$subclass = elgg_extract("subclass", $vars);
$outerId = elgg_extract("outerId", $vars);
$ratingText = elgg_extract("ratingTextClass", $vars);

?>
<div id='<?php echo $outerId; ?>' class="fivestar-ratings fivestar-ratings-<?php echo $guid . $subclass; ?>">
	<form id="fivestar-form-<?php echo $guid; ?>" action="<?php echo elgg_get_site_url(); ?>action/elggx_fivestar/rate" method="post" class='fivestar-form'>
		<?php for ($i = 1; $i <= $stars; $i++) { ?>
			<?php if (round($rating['rating']) == $i) { $checked = 'checked="checked"'; } ?>
				<input type="radio" name="rate_avg" <?php echo $checked; ?> <?php echo $disabled; ?> value="<?php echo $pps * $i; ?>" />
			<?php $checked = ''; ?>
		<?php } ?>
		<input type="hidden" name="guid" value="<?php echo $guid; ?>" />
	</form>
    
    <div class='clearfix'>
	<?php if (!elgg_extract("min", $vars, false)) { ?>
		<p <?php echo $ratingText; ?>>
			&nbsp;<span id="fivestar-rating-<?php echo $guid; ?>"><?php echo $rating['rating']; ?></span>/<?php echo $stars . ' ' . elgg_echo('elggx_fivestar:lowerstars'); ?> (<span id="fivestar-votes-<?php echo $guid; ?>"><?php echo $rating['votes'] ?></span>)
		</p>
	<?php } ?>
	</div>
</div>

<script type="text/javascript">
    elgg.elggx_fivestar.setup(<?php echo $guid; ?>);
</script>
