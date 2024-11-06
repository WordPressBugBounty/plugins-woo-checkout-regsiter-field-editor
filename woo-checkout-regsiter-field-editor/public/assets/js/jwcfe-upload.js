jQuery(document).ready(function ($) {


	$('.jwcfe_file').on('change', function () {

		var file_data = $(this).prop('files')[0];
		var form_data = new FormData();
		form_data.append('file', file_data);
		form_data.append('action', 'jwcfe_file_action');
		form_data.append('field_name', $(this).attr("name"));
		form_data.append('currentScreen', MyAjax.currentScreen);
		$.ajax({
			url: MyAjax.ajaxurl, // point to server-side PHP script 
			dataType: 'text',  // what to expect back from the PHP script, if anything
			cache: false,
			contentType: false,
			processData: false,
			data: form_data,
			type: 'post',
			beforeSend: function () {
				var loaderimg = MyAjax.loaderPath;
				$("body").append("<div class='jwcfe_spinner' style='position: fixed; color:#fff; text-transform: capitalize; top: 0; left: 0; bottom: 0; padding-top: 10%; width: 100%; text-align: center; height: 100%; background: rgba(0,0,0,0.6); right: 0;'><img style='display:inline-block;' src='" + loaderimg + "' /></div>");

			},
			success: function (php_script_response) {

				if (php_script_response == 1) {
					var loaderimg = MyAjax.donePath;
					$("body .jwcfe_spinner").html("<img style='display:inline-block;' src='" + loaderimg + "' />");

					setTimeout(function () {
						$("body .jwcfe_spinner").remove();
					}, 500)
				} else {

					var jsonArr = JSON.parse(php_script_response);
					for (var key in jsonArr) {
						if (jsonArr.hasOwnProperty(key)) {
							$("body .jwcfe_spinner").html(jsonArr[key]);
						}
					}

					setTimeout(function () {
						$("body .jwcfe_spinner").remove();
					}, 2000)
				}
			}
		});
	});


});