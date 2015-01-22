<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<h2><?php echo __( 'Tick boxes.', 'ezihosting_wc' ) ?><a href="javascript:;" class="add-new-h2" id="efw-add-boxes">Add boxes</a></h2>
<style>
	.form-table th {
		width: 40px;
	}
	.forminp input {
		width: 60px;
	}
	#efw-boxes-form {
		display: none;
	}
	.efw-editable-value {
		cursor: pointer;
	}
	.efw-editable-value em{
		font-style: normal;
	}
	.efw-editable-form {
		display: none;
	}
	.efw-editable-form a {
		display: inline-block;
		/*margin-left: 10px;*/
	}
</style>
<div id="efw-boxes-form">
	<table class="form-table">
		<tbody>
		<tr valign="top" class="">
			<th scope="row" class="titledesc">Box Name</th>
			<td class="forminp forminp-text">
				<input name="name" id="efw_box_name" type="text" class="" style="width: 100px;" />
			</td>
		</tr>
		<tr valign="top" class="">
			<th scope="row" class="titledesc">Length(cm)</th>
			<td class="forminp forminp-text">
				<input name="length" id="efw_box_length" type="text" class="attr" />
			</td>
		</tr>
		<tr valign="top" class="">
			<th scope="row" class="titledesc">Width(cm)</th>
			<td class="forminp forminp-text">
				<input name="width" id="efw_box_width" type="text" class="attr" />
			</td>
		</tr>
		<tr valign="top" class="">
			<th scope="row" class="titledesc">Height(cm)</th>
			<td class="forminp forminp-text">
				<input name="height" id="efw_box_height" type="text" class="attr" />
			</td>
		</tr>
		<tr valign="top" class="">
			<th scope="row" class="titledesc">Weight(kg)</th>
			<td class="forminp forminp-text">
				<input name="weight" id="efw_box_weight" type="text" class="attr" />
			</td>
		</tr>
		<tr valign="top" class="">
			<th scope="row" class="titledesc">Max Weight(kg)</th>
			<td class="forminp forminp-text">
				<input name="max_weight" id="efw_box_max_weight" type="text" class="attr" />
			</td>
		</tr>

		<tr valign="top" class="">
			<th scope="row" class="titledesc">Flat rate</th>
			<td class="forminp forminp-text">
				<input type="checkbox" value="1" name="efx_box_flat_rate" style="width: 16px;" />
				<input name="efx_box_rate" id="efw_box_rate" type="text" placeholder="rate" class="" style="display: none" />
			</td>
		</tr>
		<tr valign="top" class="">
			<th scope="row" class="titledesc"></th>
			<td class="forminp forminp-text" id="efw-boxes-form-op">
				<button class="button-primary" type="button">Create</button>
				<button class="button-secondary" type="button">Cancel</button>
			</td>
		</tr>
		</tbody>
	</table>
</div>


<div id="efw-boxes-result">
	<?php $list_table->display(); ?>
</div>
