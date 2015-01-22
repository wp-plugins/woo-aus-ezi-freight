(function($){
	$(function(){
		if($('#efw_ship_to_countries').size()) {
			showCountries($('#efw_ship_to_countries').val());

			$('#efw_ship_to_countries').change(function(){
				showCountries($(this).val());
			});
		}

		if($('#efw_rate_offer').size()) {
			showServicesLimit($('#efw_rate_offer').val());

			$('#efw_rate_offer').change(function(){
				showServicesLimit($(this).val());
			});
		}

		$('input[name="efx_box_flat_rate"]').click(function(){
			if($(this).prop('checked')) {
				$(this).next('input').show();
			} else {
				$(this).next('input').hide();
			}
		});

		$('#efw-add-boxes').click(function(){
			$('#efw-boxes-form').show();
		});

		$('#efw-boxes-form-op .button-secondary').click(function(){
			$('#efw-boxes-form').hide();
		});

		$('#efw-boxes-form-op .button-primary').click(function(){
			var form = $('#efw-boxes-form');
			var error = '';
			var postData = {};

			if(!form.find('input[name="name"]').val()) {
				alert('Box name is required!');
				return false;
			}
			postData.name = form.find('input[name="name"]').val();

			form.find('input.attr').each(function(){
				if(isNaN($(this).val()) || $(this).val() <= 0) {
					error = $(this).attr('name') + ' is invalid!';
					return false;
				}

				postData[$(this).attr('name')] = $(this).val();
			});
			if(error) {
				alert(error);
				return false;
			}

			var flatRate = $('input[name="efx_box_flat_rate"]');
			if(flatRate.prop('checked')) {
				var rate = $('input[name="efx_box_rate"]')

				if(isNaN(rate.val()) || rate.val() <= 0) {
					alert('Flat rate is invalid!');
					return false;
				}

				postData.rate = rate.val();
			}



			//$('#mainform').submit();
			$.ajax({
				url:ajaxurl+'?action=efw_create_box',
				data:postData,
				dataType:'json',
				type:'post',
				success: function(data){
					alert(data.msg);
					window.location.reload();
				}
			})
		});

		$('.efw-box-delete').click(function(){
			if(!confirm('Are you sure to delete this box?'))
				return false;

			var obj = $(this);
			var id = obj.attr('data-id');
			$.ajax({
				url:ajaxurl+'?action=efw_delete_box',
				data:{id:id},
				dataType:'json',
				type:'post',
				success: function(data){
					alert(data.msg);
					obj.parent().parent().remove();
				}
			})
		});


		$('.efw-editable-value').click(function(){
			$(this).hide().parent().find('.efw-editable-form').show();
		});

		$('.efw-editable-cancel').click(function(){
			$(this).parent().parent().find('.efw-editable-value').show();
			$(this).parent().hide();
		});

		$('.efw-editable-form input[type="checkbox"]').click(function(){
			if($(this).prop('checked')) {
				$(this).next('input').show();
			} else {
				$(this).next('input').val('0').hide();
			}
		});

		$('.efw-editable-update').click(function(){
			var parent = $(this).parent();
			var btn = $(this);
			var input = parent.find('input[type="text"]');

			var isRate = $(this).attr('data-type') == 'rate';

			var postData = {
				id:input.attr('data-id'),
				value:input.val(),
				key:input.attr('data-key')
			};

			if(postData.key == 'name') {
				if(!postData.value) {
					alert('Box name is required!');
					return false;
				}
			} else {
				if(!isRate || input.prev('input').prop('checked')) {
					if(isNaN(postData.value) || postData.value <= 0) {
						alert(postData.key + ' is invalid!');
						return false;
					}
				}
			}

			btn.html('Updating...');
			$.ajax({
				url:ajaxurl+'?action=efw_update_box',
				data:postData,
				dataType:'json',
				type:'post',
				success: function(data){
					if(!data.error) {
						var emHtml = isRate && postData.value == 0 ? '---' : postData.value;
						console.log(emHtml);
						parent.parent().find('.efw-editable-value em').html(emHtml);
						parent.find('.efw-editable-cancel').trigger('click');
					}
					alert(data.msg);
				},
				complete:function() {
					btn.html('Update');
				}
			})
		});
	});

	var showCountries = function(type) {
		if(type == 'all') {
			$('#efw_countries_wrap').hide();
		} else {
			$('#efw_countries_wrap').show();
		}
	};

	var showServicesLimit = function(type) {
		if(type == 'all') {
			$('#efw_services_limit_wrap').show();
		} else {
			$('#efw_services_limit_wrap').hide();
		}
	}
}(jQuery));

