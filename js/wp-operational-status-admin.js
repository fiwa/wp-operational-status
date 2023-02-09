(function ($) {
	var wpOperationalStatusAddMonitor =  $( '#wp-operational-status-add-monitor' ),
		wpOperationalStatusAddMonitorTable = $( '#wp-operational-status-current-monitors' );

	wpOperationalStatusAddMonitor.on( 'submit', function(e) {
		e.preventDefault();

		var wpOperationalStatusAddMonitorPost = {
			'url': $( 'input[name="monitor_url"]', wpOperationalStatusAddMonitor ).val(),
			'name': $( 'input[name="monitor_name"]', wpOperationalStatusAddMonitor ).val(),
			'response_code': $( 'input[name="monitor_reponse_code"]', wpOperationalStatusAddMonitor ).val(),
			'nonce': $( '#wp-operational-status-add-monitor-nonce' ).val()
		};

		// Basic validation before submitting
		if ( wpOperationalStatus.validate( wpOperationalStatusAddMonitorPost ) ) {
			wpOperationalStatus.addMonitor( wpOperationalStatusAddMonitorPost );
		}
	});

	$('a.delete', wpOperationalStatusAddMonitorTable).on( 'click', function(e) {
		e.preventDefault();

		var wpOperationalStatusAddMonitorPost = {
			'id': parseInt( $( this ).data( 'id' ), 10 ),
			'name': $( this ).data( 'name' ),
			'url': $( this ).data( 'url' ),
			'nonce': $( this ).data( 'nonce' )
		};

		if ( confirm ( wpOperationalStatusAdminScriptL10n.confirm_delete.replace( '{{name}}', wpOperationalStatusAddMonitorPost.name ) ) ) {
			if ( wpOperationalStatus.validate( wpOperationalStatusAddMonitorPost ) ) {
				wpOperationalStatus.deleteMonitor( wpOperationalStatusAddMonitorPost );
			}
		}
	});

	var wpOperationalStatus = {
		validate: function( wpOperationalStatusAddMonitorPost ) {
			if ( '' === wpOperationalStatusAddMonitorPost.nonce.trim() ) {
				alert( wpOperationalStatusAdminScriptL10n.empty_nonce );
				return false;
			}
			return true;
		},

		addMonitor: function( wpOperationalStatusAddMonitorPost ) {
			var add_ajax = $.ajax({
				type: 'post',
				dataType : 'json',
				url: wpOperationalStatusAdminScriptL10n.admin_ajax_url,
				data: 'action=wp_operational_status_admin&do=add_monitor' +
					'&url=' + wpOperationalStatusAddMonitorPost.url +
					'&name=' + wpOperationalStatusAddMonitorPost.name +
					'&response_code=' + wpOperationalStatusAddMonitorPost.response_code +
					'&_ajax_nonce=' + wpOperationalStatusAddMonitorPost.nonce,
				cache: false
			}).done( function( data ) {
				if ( data.success ) {
					$( data.html ).hide().prependTo( $( 'tbody', wpOperationalStatusAddMonitorTable) ).fadeIn();
				}
			}).fail( function( jqXHR, textStatus, errorThrown ) {
				console.log( errorThrown );
			});

			// Return promise
			return add_ajax;
		},

		deleteMonitor: function( wpOperationalStatusAddMonitorPost ) {
			var delete_ajax = $.ajax({
				type: 'post',
				dataType : 'json',
				url: wpOperationalStatusAdminScriptL10n.admin_ajax_url,
				data: 'action=wp_operational_status_admin&do=delete' +
					'&id=' + wpOperationalStatusAddMonitorPost.id +
					'&url=' + wpOperationalStatusAddMonitorPost.url +
					'&_ajax_nonce=' + wpOperationalStatusAddMonitorPost.nonce,
				cache: false
			}).done( function( data ) {
				if ( data.success ) {
					var deleteMonitor = $( '#monitor-' + data.deleted_monitor.id );

					if ( deleteMonitor.length ) {
						deleteMonitor.fadeOut( 'normal', function() {
							this.remove();
						});
					}
				}
			}).fail( function( jqXHR, textStatus, errorThrown ) {
				console.log( errorThrown );
			});

			// Return promise
			return delete_ajax;
		},
	};
}(jQuery));
