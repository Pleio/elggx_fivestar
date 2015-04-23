<?php ?>
//<script>
elgg.provide("elgg.elggx_fivestar");

elgg.elggx_fivestar.setup = function(guid) {
    $("#fivestar-form-" + guid).children().not("select").hide();

    // Create stars for: Rate this
    $("#fivestar-form-" + guid).stars({
        cancelShow: 0,
        cancelValue: 0,
        callback: function(ui, type, value) {
            // Disable Stars while AJAX connection is active
            ui.disable();

            // Display message to the user at the begining of request
            $("#fivestar-messages-" + guid).text(elgg.echo('saving')).stop().css("opacity", 1).fadeIn(30);

            elgg.action("elggx_fivestar/rate", {data:{guid: guid, vote: value}, success: function(db) {
				// Select stars from "Average rating" control to match the returned average rating value
                $("#fivestar-form-" + guid).stars("select", Math.round(db.rating));

                // Update other text controls...
                $("#fivestar-votes-" + guid).text(db.votes);
                $("#fivestar-rating-" + guid).text(db.rating);

                // Display confirmation message to the user
                if (db.msg) {
                    $("#fivestar-messages-" + guid).text(db.msg).stop().css("opacity", 1).fadeIn(30);
                } else {
                    $("#fivestar-messages-" + guid).text(elgg.echo('elggx_fivestar:rating_saved')).stop().css("opacity", 1).fadeIn(30);
                }

                // Hide confirmation message and enable stars for "Rate this" control, after 2 sec...
                setTimeout(function(){
					$("#fivestar-messages-" + guid).fadeOut(1000, function(){
						ui.enable();
					});
                }, 2000);
            }});
        }
    });

    // Create element to use for confirmation messages
    $('<div class="fivestar-messages" id="fivestar-messages-' + guid + '" />').appendTo("#fivestar-form-" + guid);
}
