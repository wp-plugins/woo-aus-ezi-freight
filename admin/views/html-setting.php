<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>


<h2>Settings</h2>
<hr />
<table class="form-table">

	<tbody>
	<tr valign="top" class="">
		<th scope="row" class="titledesc">Shipping Freight Calculations</th>
		<td class="forminp forminp-checkbox">
			<fieldset>
				<legend class="screen-reader-text"><span>Shipping Freight Calculations</span></legend>
				<label for="efw_enable_tb">
					<input name="efw_config[efw_enable_tb]" id="efw_enable_tb" type="checkbox" value="1" <?php if($config->efw_enable_tb): ?>checked="checked"<?php endif;?> /> Enable Woo Aus EZi Freight Trial
				</label>
			</fieldset>
			<fieldset class="">
				<label for="efw_show_tb">
					<input name="efw_config[efw_show_tb]" id="efw_show_tb" type="checkbox" value="1" <?php if($config->efw_show_tb): ?>checked="checked"<?php endif;?> /> Enable the shipping calculator on the cart page
				</label>
			</fieldset>
			<fieldset class="">
				<label for="efw_debug">
					<input name="efw_config[efw_debug]" id="efw_debug" type="checkbox" value="1" <?php if($config->efw_debug): ?>checked="checked"<?php endif;?> /> Enable debug mode to show debugging information on your cart/checkout
				</label>
			</fieldset>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="efw_interparcel_username">License key</label>
			<td class="forminp forminp-text">
				<input name="efw_config[efw_license_key]" id="efw_license_key" type="text" style="width:200px;" value="<?php echo $config->efw_license_key ?>" class="" />
				<?php if(!$config->efw_license_key || EFWHelper::validLicense($config->efw_license_key) !== true) :?>
				<br />
				<p style="color: #ff3635">
					Our free Woo Aus EZi Freight plugin is there for you to try it out. There will be an annoying message in your customer’s cart encouraging you to Go Pro. Going Pro will get rid of this message and activates 12 months support and free updates. You can <a href="http://www.ezihosting.com/woocommerce-australian-freight-extension/" target="_blank">Go Pro here</a>.
				</p>
				<?php endif;?>
			</td>
	</tr>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="efw_method_title">Method Title</label>
		<td class="forminp forminp-text">
			<input name="efw_config[efw_method_title]" id="efw_method_title" type="text" style="width:300px;" value="<?php echo $config->efw_method_title ?>" class="" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="efw_ship_to_countries">Countries</label>
		<td class="forminp forminp-text">
			<select name="efw_config[efw_ship_to_countries]" id="efw_ship_to_countries">
				<option value="all" <?php if($config->efw_ship_to_countries == 'all'): ?>selected="selected"<?php endif;?>>all countries</option>
				<option value="specific" <?php if($config->efw_ship_to_countries == 'specific'): ?>selected="selected"<?php endif;?>>specific countries only</option>
			</select>
		</td>
	</tr>
	<tr valign="top" id="efw_countries_wrap">
		<th scope="row" class="titledesc">
			<label for="efw_countries">Countries</label>
			<td class="forminp forminp-text">
				<select multiple="multiple" class="multiselect chosen_select" name="efw_config[efw_countries][]" id="efw_countries" data-placeholder="Select some countries">
					<?php foreach($countries as $k=>$v):?>
						<option value="<?php echo $k ?>" <?php if(in_array($k, $config->efw_countries)): ?>selected="selected"<?php endif;?>>
							<?php echo $v ?>
						</option>
					<?php endforeach;?>
				</select>
			</td>
	</tr>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="efw_packing_method">Parcel Packing Method</label>
			<td class="forminp forminp-text">
				<select name="efw_config[efw_packing_method]" id="efw_packing_method">
					<option value="all" <?php if($config->efw_packing_method == 'all'): ?>selected="selected"<?php endif;?>>weight of all items</option>
					<option value="individual" <?php if($config->efw_packing_method == 'individual'): ?>selected="selected"<?php endif;?>>individual items</option>
					<option value="box" <?php if($config->efw_packing_method == 'box'): ?>selected="selected"<?php endif;?>>box packing</option>
				</select>
			</td>
	</tr>

	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="efw_pickup_city">Origin pickup city</label>
			<td class="forminp forminp-number">
				<input name="efw_config[efw_pickup_city]" id="efw_pickup_city" type="text" value="<?php echo $config->efw_pickup_city ?>" class="" /> where the merchant ships from
			</td>
	</tr>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="efw_pickup_postcode">Origin pickup postcode</label>
		<td class="forminp forminp-number">
			<input name="efw_config[efw_pickup_postcode]" id="efw_pickup_postcode" type="text" style="width:50px;" value="<?php echo $config->efw_pickup_postcode ?>" class="" /> where the merchant ships from
		</td>
	</tr>
<!--	<tr valign="top">-->
<!--		<th scope="row" class="titledesc">-->
<!--			<label for="efw_tax_rate">Tax rate</label>-->
<!--			<td class="forminp forminp-number">-->
<!--				<input name="efw_config[efw_tax_rate]" id="efw_tax_rate" type="text" style="width:50px;" value="--><?php //echo $config->efw_tax_rate ?><!--" class="" /> %-->
<!--			</td>-->
<!--	</tr>-->
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="efw_rate_offer">Cart rates offer</label>
			<td class="forminp forminp-text">
				<select name="efw_config[efw_rate_offer]" id="efw_rate_offer">
					<option value="all" <?php if($config->efw_rate_offer == 'all'): ?>selected="selected"<?php endif;?>>show multiple rates in shopping cart</option>
					<option value="cheapest" <?php if($config->efw_rate_offer == 'cheapest'): ?>selected="selected"<?php endif;?>>only the cheapest</option>
				</select>
			</td>
	</tr>
	<tr valign="top" id="efw_services_limit_wrap">
		<th scope="row" class="titledesc">
			<label for="efw_services_limit">Shipping services limit</label>
			<td class="forminp forminp-number">
				<input name="efw_config[efw_services_limit]" id="efw_services_limit" type="number" value="<?php echo $config->efw_services_limit ?>" style="width: 50px;" class="" min="0" /> The max shipping services displayed in the shipping freight calculations (0 means no limit, display all)
			</td>
	</tr>
<!--	<tr valign="top">-->
<!--		<th scope="row" class="titledesc">-->
<!--			<label for="efw_tax_rate">Receipts</label>-->
<!--			<td class="forminp forminp-checkbox">-->
<!--				<input name="efw_config[efw_receipts]" id="efw_receipts" type="checkbox" value="1" class="" --><?php //if($config->efw_receipts): ?><!--checked="checked"--><?php //endif;?><!-- /> Requirement of receipts-->
<!--			</td>-->
<!--	</tr>-->
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="efw_tax_rate">Additional packaging days</label>
			<td class="forminp forminp-number">
				<input name="efw_config[efw_packaging_days]" id="efw_packaging_days" type="number" value="<?php echo $config->efw_packaging_days ?>" style="width: 50px;" class="" min="0" /> added to estimated delivery date
			</td>
	</tr>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="efw_handling_cost">Additional handling cost </label>
			<td class="forminp forminp-number">
				<input name="efw_config[efw_handling_cost]" id="efw_handling_cost" type="text" style="width:50px;" value="<?php echo $config->efw_handling_cost ?>" class="" /> $
			</td>
	</tr>

	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="efw_interparcel_username">Interparcel username</label>
			<td class="forminp forminp-text">
				<input name="efw_config[efw_interparcel_username]" id="efw_interparcel_username" type="text" style="width:200px;" value="<?php echo $config->efw_interparcel_username ?>" class="" autocomplete="off" />
			</td>
	</tr>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="efw_interparcel_password">Interparcel password</label>
			<td class="forminp forminp-text">
				<input name="efw_config[efw_interparcel_password]" id="efw_interparcel_password" type="password" style="width:200px;" value="<?php echo $config->efw_interparcel_password ?>" class="" autocomplete="off" />
			</td>
	</tr>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="">Register with Click & Send and Interparcel</label>
			<td class="forminp forminp-text">
				<a href="https://www.clickandsend.com.au/home/NISS_frm_home.php?sz_Client=AustraliaPost" style="margin-right: 10px;" target="_blank"><img src="http://www.ezihosting.com/wp-content/uploads/2014/11/Australian-Post-Logo-150x150.png" style="width: 75px;height: 75px" /></a>
				<a href="http://www.interparcel.com.au/new_account.php" target="_blank"><img src="http://www.ezihosting.com/wp-content/uploads/2014/11/InterParcel-Logo-150x150.png" style="width: 75px;height: 75px" /></a>
			</td>
	</tr>
	</tbody>
</table>


<br />
<br />

<h2><?php echo __( 'Box Packing.', 'ezihosting_wc' ) ?><a href="javascript:;" class="add-new-h2" id="efw-add-boxes">Add parcel</a></h2>
<hr />
<style>
	#efw-boxes-form .form-table th {
		width: 40px;
	}
	#efw-boxes-form .forminp input {
		width: 60px;
	}
	#efw-boxes-form {
		display: none;
	}
	#efw-boxes-result .efw-editable-value {
		cursor: pointer;
	}
	#efw-boxes-result .efw-editable-value em{
		font-style: normal;
	}
	#efw-boxes-result .efw-editable-form {
		display: none;
	}
	#efw-boxes-result .efw-editable-form a {
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
				<input name="name" id="efw_box_name" type="text" class="" style="width: 200px;" />
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
			<th scope="row" class="titledesc">Flat rate (ex GST)</th>
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
