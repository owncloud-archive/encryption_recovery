$( document ).ready(function() {
	OC.Notification.show('Regenerating recovery keys for files.... please leave your window open');
	$.get( OC.generateUrl('/apps/encryption_recovery/regenerate'), function( data ) {
		OC.Notification.showTemporary('Encryption recovery setup is complete!');
	});
});