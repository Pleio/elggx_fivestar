<?php
?>
.ui-stars-star,
.ui-stars-cancel {
	float: left;
	display: block;
	overflow: hidden;
	text-indent: -999em;
	cursor: pointer;
}
.ui-stars-star a,
.ui-stars-cancel a {
	width: 16px;
	height: 15px;
	display: block;
	background: url(<?php echo elgg_get_site_url(); ?>_graphics/elgg_sprites.png) no-repeat left;
}
.ui-stars-star a {
	background-position: 0 -1188px;
}
.ui-stars-star-on a {
	background-position: 0 -1206px;
}
.ui-stars-star-hover a {
	background-position: 0 -1152px;
}
.ui-stars-cancel a {
	background-position: 0 -270px;
}
.ui-stars-cancel-hover a {
	background-position: 0 -252px;
}
.ui-stars-star-disabled,
.ui-stars-star-disabled a,
.ui-stars-cancel-disabled a {
	cursor: default !important;
}

.fivestar-messages {
	margin-left: 1em;
	float: left;
	line-height: 15px;
	color: #fd1c24
}

.fivestar-form {
	width: 200px;
}
