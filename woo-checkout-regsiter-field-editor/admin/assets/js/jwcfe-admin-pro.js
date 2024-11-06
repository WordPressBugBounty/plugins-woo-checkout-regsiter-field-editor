// Polyfill for jQuery.isArray
if (typeof jQuery !== 'undefined' && !jQuery.isArray) {
    jQuery.isArray = Array.isArray;
}

const getBillingFields = WcfeAdmin.wc_fields.billing;

var jwcfe_settings = (function ($, window, document) {

	 // Polyfill for jQuery.isArray
        if (typeof jQuery !== 'undefined' && !jQuery.isArray) {
            jQuery.isArray = Array.isArray;
        }
	var MSG_INVALID_NAME = WcfeAdmin.MSG_INVALID_NAME;

	var OPTION_ROW_HTML = '<div class="jwcfe-opt-row">';

	// OPTION_ROW_HTML += '<div style="width:280px;"><input type="text" name="i_options_key[]" placeholder="Option Value" style="width280px;"/></div>';

	// OPTION_ROW_HTML += '<div style="width:280px;"><input type="text" name="i_options_text[]" placeholder="Option Text" style="width:280px;"/></div>';
	
	OPTION_ROW_HTML += '<div style="width:280px;"><input type="text" name="i_options_key[]" placeholder="Option Value" style="width:280px;" value="Default Option Value"/></div>';
	OPTION_ROW_HTML += '<div style="width:280px;"><input type="text" name="i_options_text[]" placeholder="Option Text" style="width:280px;" value="Default Option Text"/></div>';

	OPTION_ROW_HTML += '<div class="action-cell"><a href="javascript:void(0)" onclick="jwcfeAddNewOptionRow(this)" class="btn btn-blue" title="Add new option">+</a></div>';
	OPTION_ROW_HTML += '<div class="action-cell"><a href="javascript:void(0)" onclick="jwcfeRemoveOptionRow(this)" class="btn btn-red"  title="Remove option">x</a></div>';

	OPTION_ROW_HTML += '<div class="action-cell sort ui-sortable-handle">';
	OPTION_ROW_HTML += '<span class="btn btn-tiny sort ui-jwcf-sortable-handle"  onclick="jwcfe_handler_OptionRow(this)" title="Drag to sort">⇅</span>';
	OPTION_ROW_HTML += '</div>';

	OPTION_ROW_HTML += '</div>';



	/*------------------------------------
	*---- ON-LOAD FUNCTIONS - SATRT -----
	*------------------------------------*/

	$(function () {

		$('input[name=fname]').on('input', function () {
		    $(this).val(function (_, v) {
		        return v.replace(/\s+/g, '');
		    });
		});


		$(".jwcfe_tabs").tabs();
		

		$('select.jwcfe-enhanced-multi-select').select2({
			placeholder: "Plese Select",
			minimumResultsForSearch: 10,
			allowClear: true,
		}).addClass('enhanced');

		$(".jwcfe_remove_field_btn").on('click', function () {
		    var form = $(this.form);

		    $('#jwcfe_checkout_fields tbody input:checkbox[name=select_field]:checked').each(function () {
		        $(this).closest('tr').remove();
		    });
		});


		$('#jwcfe_checkout_fields tbody').sortable({
			items: 'tr',
			cursor: 'move',
			axis: 'y',
			handle: 'td.sort',
			scrollSensitivity: 40,
			helper: function (e, ui) {
				ui.children().each(function () {
					$(this).width($(this).width());
				});

				ui.css('left', '0');
				return ui;
			}

		});



		$("#jwcfe_checkout_fields tbody").on("sortstart", function (event, ui) {
			ui.item.css('background-color', '#f6f6f6');
		});


		$("#jwcfe_checkout_fields tbody").on("sortstop", function (event, ui) {
			ui.item.removeAttr('style');
			jwcfe_prepare_field_order_indexes();
		});

	});



	_saveCustomFieldForm = function saveCustomFieldForm(loaderPath, donePath) {

		var formData = $('#jwcfe_custom_options_form').serializeArray();
		var data = {
			formdata: formData,
			action: 'save_custom_form_fields'
		};

		$.ajax({
			dataType: "html",
			type: 'POST',
			url: WcfeAdmin.ajaxurl,
			data: data,
			beforeSend: function () {
				var loaderimg = loaderPath;
				$("body").append("<div class='jwcfe_spinner'><img src='" + loaderimg + "' /></div>");
			},

			success: function (data) {
				// alert(data);
				var loaderimg = donePath;
				$("body .jwcfe_spinner").html("<img src='" + loaderimg + "' />");
				setTimeout(function () {
					$("body .jwcfe_spinner").remove();
				}, 500)
			}
		})
	}

	
	
	
	function setup_enhanced_multi_select(form) {

		form.find('select.jwcfe-enhanced-multi-select2').each(function () {
			$(this).select2({
				minimumResultsForSearch: 10,
				allowClear: true,
				placeholder: $(this).data('placeholder'),
				templateSelection: function (state) {
                    if (!state.id) {
                        return state.text;
                    }
                    return $('<span>' + state.text + '</span>');
                }
			}).addClass('enhanced');

		});
	}



	_openNewFieldForm = function openNewFieldForm(tabName) {
	    if (tabName == 'billing' || tabName == 'shipping' || tabName == 'additional' || tabName == 'account') {
	        tabName = tabName + '_';
	    }

		// clear all form
		$("#jwcfe_new_field_form_pp form")[0].reset();
		// $("#jwcfe_new_field_form_pp form ul li:first a").click();
		$("#jwcfe_new_field_form_pp form ul li:first a").trigger("click");

	    var form = $("#jwcfe_new_field_form_pp");
		
		// enable field 
		form.find("input[name=fname]").prop('disabled', false).css({
			'color': '',
			'background-color': '',
			'border-color': ''
		});

	    form.find("select[name=ftype]").trigger('change');  // Replaces .change() with .trigger('change')
	    form.find("select[name=fclass]").val('form-row-wide');
	    
		$("#btnaddfield").html('Add New Field');
		$("#btnaddfield").attr('data-type','add');
		$("#btnaddfield").removeAttr('data-rowId');

		$('#jwcfe_new_field_form').find('.jwcfe-enhanced-multi-variations').remove();
		$('#jwcfe_new_field_form').find('select[name="i_rule_operator"], select[name="i_rule_operand_type"]').val("").trigger('change');
		$('#jwcfe_new_field_form').find('input[name="i_rule_operand"]').val("");
		$('#jwcfe_new_field_form #jwcfe-tab-rules_new').find('.jwcfe_rule .jwcfe_condition_set_row:not(:first)').remove();

	    openjwcfeModal();
	}

	$(document).find("#btnaddfield").on('click', function(e) {
		var type = $(this).attr('data-type');
		var form = $('#jwcfe_new_field_form');

		$('<input>').attr({
			type: 'hidden',
			vaule: 'yes',
			id: 'foo',
			name: 'save_fields'
		}).appendTo('#jwcfe_checkout_fields_form');
		
		var result;
		if(type == 'add'){
			result = jwcfe_add_new_row(form);

		}else{
		var rowId = $(this).attr('data-rowId');
			result = jwcfe_update_row(form,rowId);

		}
		
		var form = $("#jwcfe_checkout_fields_form");

		if (result) {
			form.submit();
		}
	});

	function jwcfe_add_new_row(form) {
		var name = $(form).find("input[name=fname]").val();
		var type = $(form).find("select[name=ftype]").val();
		var label = $(form).find("input[name=flabel]").val();
		var text = $(form).find("textarea[name=ftext]").val();
		var placeholder = $(form).find("input[name=fplaceholder]").val();
		var min_time = $(form).find("input[name=i_min_time]").val();
		var max_time = $(form).find("input[name=i_max_time]").val();
		var time_step = $(form).find("input[name=i_time_step]").val();
		var time_format = $(form).find("select[name=i_time_format]").val();
		var maxlength = $(form).find("input[name=fmaxlength]").val();
		var options_json = get_options(form);
		var frules_action = $(form).find("select[name=i_rules_action]").val();
		var frules_action_ajax = $(form).find("select[name=i_rules_action_ajax]").val();
		var extoptionsList = $(form).find("select[name=fextoptions]").val();
		var fieldClass = $(form).find("select[name=fclass]").val();
		var labelClass = $(form).find("input[name=flabelclass]").val();
		var access = $(form).find("input[name=faccess]").prop('checked');
		var required = $(form).find("input[name=frequired]").is(':checked');
		var isinclude = $(form).find("input[name=fisinclude]").prop('checked');
		var enabled = $(form).find("input[name=fenabled]").prop('checked');
		var showinemail = $(form).find("input[name=fshowinemail]").prop('checked');
		var showinorder = $(form).find("input[name=fshowinorder]").prop('checked');
		var validations = $(form).find("select[name=fvalidate]").val();


		var err_msgs = '';

		if (name == '') {
			err_msgs = 'Name is required';
		} else if (!isHtmlIdValid(name)) {
			err_msgs = MSG_INVALID_NAME;
		} else if (type == '') {
			err_msgs = 'Type is required';
		}

		if(label == ''){
			label = name;
		}

		if (err_msgs != '') {
			$(form).find('.err_msgs').html(err_msgs);
			return false;
		}

		access = access ? 1 : 0;
		required = required ? 1 : 0;
		isinclude = isinclude ? 1 : 0;
		enabled = enabled ? 1 : 0;
		showinemail = showinemail ? 1 : 0;
		showinorder = showinorder ? 1 : 0;
		validations = validations ? validations : '';
		extoptionsList = extoptionsList ? extoptionsList : '';
		text = text.replace(/"/g, "\\'");



		var index = $('#jwcfe_checkout_fields tbody tr').size();

		var newRow = '<tr class="row_' + index + '">';

		newRow += '<td width="1%" class="sort ui-sortable-handle">';

		newRow += '<input type="hidden" name="f_order[' + index + ']" class="f_order" value="' + index + '" />';

		newRow += '<input type="hidden" name="f_custom[' + index + ']" class="f_custom" value="1" />';

		newRow += '<input type="hidden" name="f_name[' + index + ']" class="f_name" value="' + name + '" />';

		newRow += '<input type="hidden" name="f_name_new[' + index + ']" class="f_name_new" value="' + name + '" />';

		newRow += '<input type="hidden" name="f_type[' + index + ']" class="f_type" value="' + type + '" />';

		newRow += '<input type="hidden" name="f_label[' + index + ']" class="f_label" value="' + label + '" />';

		newRow += '<input type="hidden" name="f_text[' + index + ']" class="f_text" value="' + text + '" />';

		newRow += '<input type="hidden" name="f_placeholder[' + index + ']" class="f_placeholder" value="' + placeholder + '" />';
		newRow += '<input type="hidden" name="f_maxlength[' + index + ']" class="f_maxlength" value="' + maxlength + '" />';

		newRow += '<input type="hidden" name="f_options[' + index + ']" class="f_options" value="' + options_json + '" />';

		newRow += '<input type="hidden" name="f_rules_action[' + index + ']" class="f_rules_action" value="' + frules_action + '" />';

		newRow += '<input type="hidden" name="f_rules_action_ajax[' + index + ']" class="f_rules_action_ajax" value="' + frules_action_ajax + '" />';

		newRow += '<input type="hidden" name="f_extoptions[' + index + ']" class="f_extoptions" value="' + extoptionsList + '" />';

		newRow += '<input type="hidden" name="f_class[' + index + ']" class="f_class" value="' + fieldClass + '" />';

		newRow += '<input type="hidden" name="f_label_class[' + index + ']" class="f_label_class" value="' + labelClass + '" />';

		newRow += '<input type="hidden" name="f_access[' + index + ']" class="f_access" value="' + access + '" />';

		newRow += '<input type="hidden" name="f_required[' + index + ']" class="f_required" value="' + required + '" />';

		newRow += '<input type="hidden" name="f_is_include[' + index + ']" class="f_is_include" value="' + isinclude + '" />';

		newRow += '<input type="hidden" name="f_enabled[' + index + ']" class="f_enabled" value="' + enabled + '" />';

		newRow += '<input type="hidden" name="f_show_in_email[' + index + ']" class="f_show_in_email" value="' + showinemail + '" />';
		newRow += '<input type="hidden" name="f_show_in_order[' + index + ']" class="f_show_in_order" value="' + showinorder + '" />';

		newRow += '<input type="hidden" name="i_min_time[' + index + ']" class="i_min_time" value="' + min_time + '" />';
		newRow += '<input type="hidden" name="i_max_time[' + index + ']" class="i_max_time" value="' + max_time + '" />';
		newRow += '<input type="hidden" name="i_time_step[' + index + ']" class="i_time_step" value="' + time_step + '" />';
		newRow += '<input type="hidden" name="i_time_format[' + index + ']" class="i_time_format" value="' + time_format + '" />';
		newRow += '<input type="hidden" name="f_validation[' + index + ']" class="f_validation" value="' + validations + '" />';
		newRow += '<input type="hidden" name="f_deleted[' + index + ']" class="f_deleted" value="0" />';
		newRow += '</td>';
		newRow += '<td ><input type="checkbox" /></td>';
		newRow += '<td class="name">' + name + '</td>';
		newRow += '<td class="id">' + type + '</td>';
		newRow += '<td>' + label + '</td>';
		newRow += '<td>' + placeholder + '</td>';
		newRow += '<td>' + validations + '</td>';

		if (required == true) {
			newRow += '<td class="status"><span class="status-enabled tips">Yes</span></td>';
		} else {
			newRow += '<td class="status">-</td>';
		}

		if (enabled == true) {
			newRow += '<td class="status"><span class="status-enabled tips">Yes</span></td>';
		} else {
			newRow += '<td class="status">-</td>';
		}

		newRow += '<td><button type="button" onclick="openEditFieldForm(this,' + index + ')">Edit</button></td>';
		newRow += '</tr>';

		$('#jwcfe_checkout_fields tbody tr:last').after(newRow);
		return true;
	}



	/*----------------------------------------------

   *---- CONDITIONAL RULES FUNCTIONS - SATRT -----

   *----------------------------------------------*/


	var OP_AND_HTML = '<label class="thpl_logic_label">AND</label>';

	OP_AND_HTML += '<a href="javascript:void(0)" onclick="jwcfeRemoveRuleRow(this)" class="thpl_logic_link" title="Remove">X</a>';

	var OP_OR_HTML = '<tr class="thpl_logic_label_or"><td colspan="4" align="center">O R</td></tr>';

	var OP_HTML = '<a href="javascript:void(0)" class="thpl_logic_link" onclick="jwcfeAddNewConditionRow(this, 2)" title="">+</a>';

	OP_HTML += '<a href="javascript:void(0)" onclick="jwcfeRemoveRuleRow(this)" class="thpl_logic_link" title="Remove">X</a>';

	var CONDITION_HTML = '', CONDITION_SET_HTML = '', CONDITION_SET_HTML_WITH_OR = '', RULE_HTML = '', RULE_SET_HTML = '';



	$(function () {
		CONDITION_HTML = '<tr class="jwcfe_condition condition-rule-div">';

		CONDITION_HTML += '<td width="25%" class="thpladmin_rule_operand"><input type="text" name="i_rule_operand" style="width:200px;"/></td>';

		CONDITION_HTML += '<td class="actions">' + OP_HTML + '</td></tr>';

		CONDITION_SET_HTML = '<tr class="jwcfe_condition_set_row"><td>';
		CONDITION_SET_HTML += '<table class="jwcfe_condition_set" width="100%" style=""><tbody>' + CONDITION_HTML + '</tbody></table>';
		CONDITION_SET_HTML += '</td></tr>';

		CONDITION_SET_HTML_WITH_OR = '<tr class="jwcfe_condition_set_row"><td>';
		CONDITION_SET_HTML_WITH_OR += '<table class="jwcfe_condition_set" width="100%" style=""><thead>' + OP_OR_HTML + '</thead><tbody>' + CONDITION_HTML + '</tbody></table>';
		CONDITION_SET_HTML_WITH_OR += '</td></tr>';

		RULE_HTML = '<tr class="jwcfe_rule_row"><td>';
		RULE_HTML += '<table class="jwcfe_rule" width="100%" style=""><tbody>' + CONDITION_SET_HTML + '</tbody></table>';
		RULE_HTML += '</td></tr>';

		RULE_SET_HTML = '<tr class="jwcfe_rule_set_row"><td>';
		RULE_SET_HTML += '<table class="jwcfe_rule_set" width="100%"><tbody>' + RULE_HTML + '</tbody></table>';
		RULE_SET_HTML += '</td></tr>';

	});


	
	// Event listener for variation selection
	$(document).on('change', 'select[name="product_variation"]', function () {
		var selected_variation_attribute = $(this).val();
		// Update display based on selected_variation_attribute
	});


	_openEditFieldForm = function openEditFieldForm(elm, rowId) {

		var row = $(elm).closest('tr');
		var name = row.find(".f_name").val();
		// $("#jwcfe_new_field_form_pp form ul li:first a").click();
		$("#jwcfe_new_field_form_pp form ul li:first a").trigger("click");
		
		var is_custom = row.find(".f_custom").val();
		var type = row.find(".f_type").val();
		var label = row.find(".f_label").val();
		var text = row.find(".f_text").val();
		var placeholder = row.find(".f_placeholder").val();

		var min_time = row.find(".i_min_time").val();
		var max_time = row.find(".i_max_time").val();
		var time_step = row.find(".i_time_step").val();
		var time_format = row.find(".i_time_format").val();
		var maxlength = row.find(".f_maxlength").val();
		var optionsList = row.find(".f_options").val();
		var extoptionsList = row.find(".f_extoptions").val();
		var field_classes = row.find(".f_class").val();
		var label_classes = row.find(".f_label_class").val();
		var access = row.find(".f_access").val();
		var required = row.find(".f_required").val();
		var isinclude = row.find(".f_is_include").val();
		var frules_action = row.find(".f_rules_action").val();
		var frules_action_ajax = row.find(".f_rules_action_ajax").val();

		var enabled = row.find(".f_enabled").val();
		var validations = row.find(".f_validation").val();

		var showinemail = row.find(".f_show_in_email").val();
		var showinorder = row.find(".f_show_in_order").val();

		text = text.replace(/"/g, "\\'");

		is_custom = is_custom == 1 ? true : false;
		access = access == 1 ? true : false;
		required = required == 1 ? true : false;
		isinclude = isinclude == 1 ? true : false;
		enabled = enabled == 1 ? true : false;

		extoptionsList = extoptionsList.split(",");
		validations = validations.split(",");

		showinemail = showinemail == 1 ? true : false;
		showinorder = showinorder == 1 ? true : false;
		showinemail = is_custom == true ? showinemail : true;
		showinorder = is_custom == true ? showinorder : true;



		var form = $("#jwcfe_new_field_form_pp");

		form.find('.err_msgs').html('');
		form.find("input[name=rowId]").val(rowId);
		form.find("input[name=fname]").val(name);
		form.find("input[name=fnameNew]").val(name);
		form.find("select[name=ftype]").val(type);
		form.find("input[name=flabel]").val(label);
		form.find("textarea[name=ftext]").val(text);
		form.find("input[name=fplaceholder]").val(placeholder);
		form.find("input[name=fmaxlength]").val(maxlength);


		var optionsJson = row.find(".f_options").val();
		populate_options_list(form, optionsJson);
		form.find("input[name=i_min_time]").val(min_time);
		form.find("input[name=i_max_time]").val(max_time);
		form.find("input[name=i_max_time]").val(max_time);
		form.find("select[name=i_time_format]").val(time_format);
		form.find("select[name=fextoptions]").val(extoptionsList).trigger("change");
		form.find("select[name=fclass]").val(field_classes);
		form.find("input[name=flabelclass]").val(label_classes);
		form.find("select[name=fvalidate]").val(validations).trigger("change");
		form.find("input[name=faccess]").prop('checked', access);
		form.find("input[name=frequired]").prop('checked', required);
		form.find("input[name=fisinclude]").prop('checked', isinclude);
		form.find("input[name=fenabled]").prop('checked', enabled);
		form.find("input[name=fshowinemail]").prop('checked', showinemail);
		form.find("input[name=fshowinorder]").prop('checked', showinorder);


		var rulesActionAjax = frules_action_ajax;
		var rulesAction = frules_action;


		rulesAction = rulesAction != '' ? rulesAction : 'show';
		rulesActionAjax = rulesActionAjax != '' ? rulesActionAjax : 'show';


		form.find("select[name=i_rules_action]").val(rulesAction);
		form.find("select[name=i_rules_action_ajax]").val(rulesActionAjax);


		var conditionalRules = row.find(".f_rules").val();
		var conditionalRulesAjax = row.find(".f_rules_ajax").val();

		if(conditionalRules){
			populate_conditional_rules(form, conditionalRules, false);
		}
		if(conditionalRulesAjax){
			populate_conditional_rules(form, conditionalRulesAjax, true);
		}


		$(document).find('.jwcfe-enhanced-multi-select2[name=i_rule_operand]').each(function(){
			var has_selected = [];
			$(this).find('option').each(function() {
				var getIdselected = $(this).attr('data-isselected');

				if(getIdselected && getIdselected == 'yes'){
					$(this).prop('selected',true);
				}
				
				
			});

			$(this).trigger('change');
			
		});

		
		form.find("select[name=ftype]").change();

		$("#btnaddfield").html('Update Field');
		$("#btnaddfield").attr('data-type','update');
		$("#btnaddfield").attr('data-rowId',rowId);

		openjwcfeModal();


		
		form.find("input[name=fnameNew]").prop('disabled', true).css({
			'color': 'rgb(209 209 209)',
			'background-color': 'rgb(249 249 249)',
			'border-color': 'rgb(240 240 240)'
		});

		
		form.find("input[name=fname]").prop('disabled', true).css({
			'color': 'rgb(209 209 209)',
			'background-color': 'rgb(249 249 249)',
			'border-color': 'rgb(240 240 240)'
		});

		if (is_custom == false) {
			form.find("select[name=ftype]").prop('disabled', true);
			form.find("input[name=fshowinemail]").prop('disabled', true);
			form.find("input[name=fshowinorder]").prop('disabled', true);
			form.find("input[name=flabel]").focus();

		} else {
			form.find("select[name=ftype]").prop('disabled', false);
			form.find("input[name=fshowinemail]").prop('disabled', false);
			form.find("input[name=fshowinorder]").prop('disabled', false);
		}
	}



	function jwcfe_update_row(form, rowId_) {	
		var rowId = $(form).find("input[name=rowId]").val();
		var name = $(form).find("input[name=fname]").val();
		
		var type = $(form).find("select[name=ftype]").val();

		var label = $(form).find("input[name=flabel]").val();
		var text = $(form).find("textarea[name=ftext]").val();
		var placeholder = $(form).find("input[name=fplaceholder]").val();
		var min_time = $(form).find("input[name=i_min_time]").val();
		var max_time = $(form).find("input[name=i_max_time]").val();
		var time_step = $(form).find("input[name=i_time_step]").val();
		var time_format = $(form).find("select[name=i_time_format]").val();
		var frules_action = $(form).find("select[name=i_rules_action]").val();
		var frules_action_ajax = $(form).find("select[name=i_rules_action_ajax]").val();
		var extoptionsList = $(form).find("select[name=fextoptions]").val();
		var fieldClass = $(form).find("select[name=fclass]").val();
		var labelClass = $(form).find("input[name=flabelclass]").val();
		var access = $(form).find("input[name=faccess]").prop('checked');
		var maxlength = $(form).find("input[name=fmaxlength]").val();
		var enabled = $(form).find("input[name=fenabled]").prop('checked');
		var required = $(form).find("input[name=frequired]").prop('checked');
		var isinclude = $(form).find("input[name=fisinclude]").prop('checked');
		var showinemail = $(form).find("input[name=fshowinemail]").prop('checked');
		var showinorder = $(form).find("input[name=fshowinorder]").prop('checked');
		var validations = $(form).find("select[name=fvalidate]").val();

		var err_msgs = '';


		if (name == '') {
			err_msgs = 'Name is required';
		} else if (!isHtmlIdValid(name)) {
			err_msgs = MSG_INVALID_NAME;
		} else if (type == '') {
			err_msgs = 'Type is required';
		}

		if (err_msgs != '') {
			$(form).find('.err_msgs').html(err_msgs);
			return false;
		}


		access = access ? 1 : 0;
		required = required ? 1 : 0;
		isinclude = isinclude ? 1 : 0;
		enabled = enabled ? 1 : 0;
		showinemail = showinemail ? 1 : 0;
		showinorder = showinorder ? 1 : 0;
		validations = validations ? validations : '';
		extoptionsList = extoptionsList ? extoptionsList : '';

		var row = $('#jwcfe_checkout_fields tbody').find('.row_' + rowId_);
		row.find(".f_name").val(name);
		row.find(".f_type").val(type);
		row.find(".f_label").val(label);
		row.find(".f_text").val(text);
		row.find(".f_placeholder").val(placeholder);
		row.find(".i_min_time").val(min_time);
		row.find(".i_max_time").val(max_time);
		row.find(".i_time_step").val(time_step);
		row.find(".i_time_format").val(time_format);
		row.find(".f_maxlength").val(maxlength);
		row.find(".f_rules_action").val(frules_action);
		row.find(".f_rules_action_ajax").val(frules_action_ajax);

		var options_json = get_options(form);

		row.find(".f_options").val(options_json);
		row.find(".f_extoptions").val(extoptionsList);
		row.find(".f_class").val(fieldClass);
		row.find(".f_label_class").val(labelClass);
		row.find(".f_access").val(access)
		row.find(".f_required").val(required);
		row.find(".f_is_include").val(isinclude);
		row.find(".f_enabled").val(enabled);
		row.find(".f_show_in_email").val(showinemail);
		row.find(".f_show_in_order").val(showinorder);
		row.find(".f_validation").val(validations);
		row.find(".td_name").html(name);
		row.find(".td_type").html(type);
		row.find(".td_label").html(label);
		row.find(".td_placeholder").html(placeholder);
		row.find(".td_validate").html("" + validations + "");
		row.find(".td_required").html(required == 1 ? '<span class="status-enabled tips">Yes</span>' : '-');
		row.find(".td_enabled").html(enabled == 1 ? '<span class="status-enabled tips">Yes</span>' : '-');
		return true;
	}




	_removeSelectedFields = function removeSelectedFields() {
		$('#jwcfe_checkout_fields tbody tr').removeClass('strikeout');
		$('#jwcfe_checkout_fields tbody input:checkbox[name=select_field]:checked').each(function () {

			//$(this).closest('tr').remove();

			var row = $(this).closest('tr');
			if (!row.hasClass("strikeout")) {
				row.addClass("strikeout");
				row.fadeOut();
			}

			row.find(".f_deleted").val(1);
			row.find(".f_edit_btn").prop('disabled', true);
			//row.find('.sort').removeClass('sort');
		});
	}

	_enableDisableSelectedFields = function enableDisableSelectedFields(enabled) {
		$('#jwcfe_checkout_fields tbody input:checkbox[name=select_field]:checked').each(function () {
			var row = $(this).closest('tr');
			if (enabled == 0) {
				if (!row.hasClass("jwcfe-disabled")) {
					row.addClass("jwcfe-disabled");
				}
			} else {
				if (!row.hasClass("jwcfe-disabled")) {
					alert("Field is already enabled.");
				}
				row.removeClass("jwcfe-disabled");
			}

			row.find(".f_edit_btn").prop('disabled', enabled == 1 ? false : true);

			row.find(".td_enabled .toggle-checkbox").prop("checked", enabled == 1);
			row.find(".td_enabled .toggle-label").text(enabled == 1 ? 'Yes' : 'No');


			row.find(".f_enabled").val(enabled);

		});
	}

	function handleToggleSwitch(row) {
		var inputField = row.find(".td_enabled .toggle-label");
		var toggleSwitch = row.find(".td_enabled .toggle-checkbox");

		var isEnabled = toggleSwitch.prop('checked');

		if (!isEnabled) {
			if (!row.hasClass("jwcfe-disabled")) {
				row.addClass("jwcfe-disabled");
			}

			inputField.hide();
			// alert("Field is disabled.");

		} else {
			row.removeClass("jwcfe-disabled");
			inputField.show();
			// alert("Field is already enabled.");
		}

		row.find(".f_edit_btn").prop('disabled', !isEnabled);

		row.find(".td_enabled .toggle-label").text(isEnabled ? 'Yes' : 'No');

		row.find(".f_enabled").val(isEnabled ? 1 : 0);
	}

	$('.td_enabled .toggle-checkbox').on('change', function () {
		var row = $(this).closest('tr');
		handleToggleSwitch(row);
	});

	_enableDisableSelectedFields = function enableDisableSelectedFields(enabled) {
		$('#jwcfe_checkout_fields tbody input:checkbox[name=select_field]:checked').each(function () {
			var row = $(this).closest('tr');
			// Set the state of the toggle switch
			row.find(".td_enabled .toggle-checkbox").prop("checked", enabled == 1);
			handleToggleSwitch(row);
		});
	}


	// Get modal element
		var jwcfemodal = document.getElementById('jwcfeModal');
		// Get open modal button
		var jwcfemodalBtn = document.getElementById('openModalBtn');
		// Get close button
		var jwcfecloseBtn = document.getElementsByClassName('jwcfecloseBtn')[0];


		// Listen for close click 
		jwcfecloseBtn.addEventListener('click', closejwcfeModal);


		// Listen for outside click
		window.addEventListener('click', outsideClick);

		// Function to open modal
		function openjwcfeModal() {
		    jwcfemodal.style.display = 'block';
		}


		// Function to close modal
		function closejwcfeModal() {
		    jwcfemodal.style.display = 'none';
		}

		// Function to close modal if outside click
		function outsideClick(e) {
		    if (e.target == jwcfemodal) {
		        jwcfemodal.style.display = 'none';
		    }
		}

		// Listen for close click from button 
		var btncancel = document.getElementsByClassName('btncancel')[0];
		btncancel.addEventListener('click', closejwcfeModal);


	/*------------------------------------

	*---- OPTIONS FUNCTIONS - SATRT -----

	*------------------------------------*/

	function get_options(elm) {

		var optionsKey = $(elm).find("input[name='i_options_key[]']").map(function () { return $(this).val(); }).get();
		var optionsText = $(elm).find("input[name='i_options_text[]']").map(function () { return $(this).val(); }).get();
		
		var optionsSize = optionsText.length;
		var optionsArr = [];

		for (var i = 0; i < optionsSize; i++) {

			var optionDetails = {};

			optionDetails["key"] = optionsKey[i];
			optionDetails["text"] = optionsText[i];
			
			optionsArr.push(optionDetails);

		}



		var optionsJson = optionsArr.length > 0 ? JSON.stringify(optionsArr) : '';
		optionsJson = encodeURIComponent(optionsJson);
		//optionsJson = optionsJson.replace(/"/g, "'");
		return optionsJson;
	}
	// Function to handle keydown event for selecting text with Ctrl + A
	// function enableSelectAllInputFields() {
	// 	// Target all input fields with name "i_options_key[]" and "i_options_text[]"
	// 	const inputs = document.querySelectorAll('input[name="i_options_key[]"], input[name="i_options_text[]"]');

	// 	inputs.forEach(input => {
	// 		input.addEventListener('keydown', function(event) {
	// 			if (event.ctrlKey && event.key === 'a') { // Check for Ctrl + A
	// 				event.preventDefault(); // Prevent default behavior
	// 				this.select(); // Select the text in the input field
	// 			}
	// 		});
	// 	});
	// }


	// function populate_options_list(elm, optionsJson) {

	// 	var optionsHtml = "";

	// 	if (optionsJson) {
	// 		try {
	// 			optionsJson = decodeURIComponent(optionsJson);
	// 			var optionsList = $.parseJSON(optionsJson);

	// 			if (optionsList) {

	// 				jQuery.each(optionsList, function () {

						

	// 					var newkey = this.key.split('+').join(' ');

	// 					var newtxt = this.text.split('+').join(' ');

	// 					var html = '<div class="jwcfe-opt-row">';

	// 					html += '<div style="width:280px;"><input type="text" name="i_options_key[]" value="' + newkey + '" placeholder="Option Value" style="width:280px;"/></div>';

	// 					html += '<div style="width:280px;"><input type="text" name="i_options_text[]" value="' + newtxt + '" placeholder="Option Text" style="width:280px;"/></div>';

	// 					html += '<div class="action-cell"><a href="javascript:void(0)" onclick="jwcfeAddNewOptionRow(this)" class="btn btn-blue" title="Add new option">+</a></div>';

	// 					html += '<div class="action-cell"><a href="javascript:void(0)" onclick="jwcfeRemoveOptionRow(this)" class="btn btn-red"  title="Remove option">x</a></div>';

	// 					html += '<div class="action-cell sort ui-sortable-handle">';
	// 					html += '<span class="btn btn-tiny sort ui-jwcf-sortable-handle"  onclick="jwcfe_handler_OptionRow(this)" title="Drag to sort">⇅</span>';
	// 					html += '</div>';

	// 					html += '</div>';
	// 					optionsHtml += html;
	// 				});
	// 			}
	// 		} catch (err) {
	// 			alert(err);
	// 		}
	// 	}


	// 	var optionsTable = $(elm).find(".jwcfe-option-list .jwcfe-opt-container");
	// 	if (optionsHtml) {
	// 		optionsTable.html(optionsHtml);
	// 	} else {
	// 		optionsTable.html(OPTION_ROW_HTML);
	// 	}
	// 	enableSelectAllInputFields();
	// }
	document.addEventListener('DOMContentLoaded', function() {
		enableSelectAllInputFields(); // Ensure binding is applied when the DOM is ready
	});
	
	function enableSelectAllInputFields() {
		// Target all input fields with name "i_options_key[]" and "i_options_text[]"
		const inputs = document.querySelectorAll('input[name="i_options_key[]"], input[name="i_options_text[]"]');
		
		inputs.forEach(input => {
			input.addEventListener('keydown', function(event) {
				if (event.ctrlKey && event.key === 'a') { // Check for Ctrl + A
					event.preventDefault(); // Prevent default behavior
					input.select(); // Select the text in the input field
				}
			});
		});
	}
	
	function populate_options_list(elm, optionsJson) {
		let optionsHtml = "";
	
		if (optionsJson) {
			try {
				optionsJson = decodeURIComponent(optionsJson);
				let optionsList = JSON.parse(optionsJson);
	
				if (optionsList) {
					optionsList.forEach(option => {
						const newkey = option.key.split('+').join(' ');
						const newtxt = option.text.split('+').join(' ');
	
						optionsHtml += `
							<div class="jwcfe-opt-row">
								<div style="width:280px;">
									<input type="text" name="i_options_key[]" value="${newkey}" placeholder="Option Value" style="width:280px;"/>
								</div>
								<div style="width:280px;">
									<input type="text" name="i_options_text[]" value="${newtxt}" placeholder="Option Text" style="width:280px;"/>
								</div>
								<div class="action-cell">
									<a href="javascript:void(0)" onclick="jwcfeAddNewOptionRow(this)" class="btn btn-blue" title="Add new option">+</a>
								</div>
								<div class="action-cell">
									<a href="javascript:void(0)" onclick="jwcfeRemoveOptionRow(this)" class="btn btn-red" title="Remove option">x</a>
								</div>
								<div class="action-cell sort ui-sortable-handle">
									<span class="btn btn-tiny sort ui-jwcf-sortable-handle" onclick="jwcfe_handler_OptionRow(this)" title="Drag to sort">⇅</span>
								</div>
							</div>`;
					});
				}
			} catch (err) {
				alert("Error parsing options: " + err.message);
			}
		}
	
		const optionsTable = $(elm).find(".jwcfe-option-list .jwcfe-opt-container");
		optionsTable.html(optionsHtml || OPTION_ROW_HTML);
	
		enableSelectAllInputFields(); // Reapply binding for Ctrl + A to new inputs
	}
	

	addNewOptionRow = function addNewOptionRow(elm) {

		var ptable = $(elm).closest('.jwcfe-option-list');
		var optionsSize = ptable.find('.jwcfe-opt-row').size();

		if (optionsSize > 0) {
			ptable.find('.jwcfe-opt-row:last').after(OPTION_ROW_HTML);
		} else {
			ptable.append(OPTION_ROW_HTML);
		}
		
	}


	removeOptionRow = function removeOptionRow(elm) {
		var ptable = $(elm).closest('.jwcfe-option-list');
		$(elm).closest('.jwcfe-opt-row').remove();
		var optionsSize = ptable.find('.jwcfe-opt-row').size();

		if (optionsSize == 0) {
			ptable.append(OPTION_ROW_HTML);
		}
	}

	/*------------------------------------
 
	 *---- OPTIONS FUNCTIONS - END -------
 
	 *------------------------------------*/


	function jwcfe_prepare_field_order_indexes() {
		$('#jwcfe_checkout_fields tbody tr').each(function (index, el) {
			$('input.f_order', el).val(parseInt($(el).index('#jwcfe_checkout_fields tbody tr')));
		});
	};
	

	_fieldTypeChangeListner = function fieldTypeChangeListner(elm) {

		var type = $(elm).val();
		var form = $(elm).closest('form');
		showAllFields(form);

		if (type === 'select' || type === 'multiselect' ||  type === 'checkboxgroup') {
			form.find('.rowValidate').hide();
			form.find('.rowPricing').hide();
			form.find('.rowPlaceholder').hide();
			form.find('.rowMaxlength').hide();
			form.find('.rowCustomText').hide();
			form.find('.rowLabel').show();
			form.find('.rowDescription').show();
			form.find('.rowOptions').show();
			form.find('.rowClass').show();
			form.find('.pricetxt').hide();
			form.find('.taxtxt').hide();
			
			form.find('.rowLabel1').appendTo('.jwcfe_left_col_child_div');

		}
		else if (type === 'radio') {
			
			form.find('.rowValidate').hide();
			form.find('.rowPricing').hide();
			form.find('.rowPlaceholder').hide();
			form.find('.rowMaxlength').hide();
			form.find('.rowCustomText').hide();
			form.find('.rowLabel').show();
			form.find('.rowDescription').show();
			form.find('.rowOptions').show();
			form.find('.rowClass').show();
			form.find('.pricetxt').hide();
			form.find('.taxtxt').hide();
			form.find('.rowDescription2').hide();

			
		}
		else if (type === 'text') {
			form.find('.rowLabel1').hide();
			form.find('.rowDescription2').hide();
			form.find('.rowOptions').hide();
			form.find('.rowMaxlength').show();
			form.find('.rowDescription').show();
			form.find('.rowValidate').show();
			form.find('.rowClass').show();
			form.find('.pricetxt').hide();
			form.find('.taxtxt').hide();
			
		}
		else if (type === 'checkbox') {
			form.find('.rowDescription2').hide();
			form.find('.rowRequired').hide();
			form.find('.rowAccess').hide();
			form.find('.rowMaxlength').hide();
			form.find('.rowValidate').hide();
			form.find('.rowCustomText').hide();
			form.find('.rowOptions').hide();
			form.find('.rowPlaceholder').hide();
			form.find('.rowDescription').show();
			form.find('.rowClass').show(); //this is for field width

		}
		else if (type === 'textarea') {
			form.find('.rowDescription2').hide();
			form.find('.rowLabel1').hide();
			form.find('.rowDescription2').hide();
			form.find('.rowOptions').hide();
			form.find('.rowMaxlength').hide();
			form.find('.rowValidate').hide();

		}
		else if (type === 'hidden') {
			form.find('.rowDescription2').hide();

			form.find('.rowRequired').hide();
			form.find('.rowAccess').hide();
			form.find('.rowMaxlength').hide();
			form.find('.rowValidate').hide();
			form.find('.rowCustomText').hide();
			form.find('.rowOptions').hide();
			form.find('.rowPlaceholder').hide();
			form.find('.rowDescription').hide();
			form.find('.rowClass').hide(); //this is for field width
			form.find('.rowLabel1').hide();
			// form.find('.rowName').hide();
			form.find('.rowLabel').hide();


		}
		else if (type === 'heading') {
			form.find('.rowDescription2').hide();

			form.find('.rowRequired').hide();
			form.find('.rowAccess').hide();
			form.find('.rowMaxlength').hide();
			form.find('.rowValidate').hide();
			form.find('.rowCustomText').hide();
			form.find('.rowOptions').hide();
			form.find('.rowPlaceholder').hide();
			form.find('.rowDescription').show();
			form.find('.rowClass').show(); //this is for field width

		}
		else if (type === 'paragraph') {
			form.find('.rowDescription2').hide();

			form.find('.rowRequired').hide();
			form.find('.rowAccess').hide();
			form.find('.rowMaxlength').hide();
			form.find('.rowValidate').hide();
			form.find('.rowCustomText').hide();
			form.find('.rowOptions').hide();
			form.find('.rowPlaceholder').show();
			form.find('.rowDescription').show();
			form.find('.rowClass').show(); //this is for field width


		} 
		else if (type === 'email') {
			form.find('.rowLabel1').hide();
			form.find('.rowDescription2').hide();
			form.find('.rowOptions').hide();
			form.find('.rowMaxlength').hide();
			form.find('.rowDescription').show();
			form.find('.rowValidate').show();
			form.find('.rowClass').show();
			form.find('.rowDescription2').hide();

		} 
		else if (type === 'phone') {
			form.find('.rowLabel1').hide();
			form.find('.rowDescription2').hide();
			form.find('.rowOptions').hide();
			form.find('.rowMaxlength').hide();
			form.find('.rowDescription').show();
			form.find('.rowValidate').show();
			form.find('.rowClass').show();
			form.find('.rowDescription2').hide();

		} 
		else if (type === 'password') {
			form.find('.rowRequired').show();
			form.find('.rowValidate').hide();
			form.find('.rowCustomText').hide();
			form.find('.rowOptions').hide();
			form.find('.rowAccess').show();
			form.find('.rowClass').show();
			form.find('.rowLabel1').hide();
			form.find('.rowDescription2').hide();
			form.find('.rowMaxlength').show();
			form.find('.rowDescription2').hide();

		} 
		else if (type === 'timepicker') {
			form.find('.rowRequired').hide();
			form.find('.rowAccess').hide();
			form.find('.rowMaxlength').hide();
			form.find('.rowValidate').hide();
			form.find('.rowCustomText').hide();
			form.find('.rowOptions').hide();
			form.find('.rowPlaceholder').hide();
			form.find('.rowDescription').show();
			form.find('.rowClass').show(); //this is for field width
			form.find('.rowDescription2').hide();

		} 
		else if (type === 'date') {
			form.find('.rowRequired').hide();
			form.find('.rowAccess').hide();
			form.find('.rowMaxlength').hide();
			form.find('.rowValidate').hide();
			form.find('.rowCustomText').hide();
			form.find('.rowOptions').hide();
			form.find('.rowPlaceholder').hide();
			form.find('.rowDescription').show();
			form.find('.rowClass').show(); //this is for field width
			form.find('.rowDescription2').hide();

			
		} 
		else if (type === 'month') {
			form.find('.rowRequired').hide();
			form.find('.rowAccess').hide();
			form.find('.rowMaxlength').hide();
			form.find('.rowValidate').hide();
			form.find('.rowCustomText').hide();
			form.find('.rowOptions').hide();
			form.find('.rowPlaceholder').hide();
			form.find('.rowDescription').show();
			form.find('.rowClass').show(); //this is for field width
			form.find('.rowDescription2').hide();


		} 
		else if (type === 'week') {
			form.find('.rowRequired').hide();
			form.find('.rowAccess').hide();
			form.find('.rowMaxlength').hide();
			form.find('.rowValidate').hide();
			form.find('.rowCustomText').hide();
			form.find('.rowOptions').hide();
			form.find('.rowPlaceholder').hide();
			form.find('.rowDescription').show();
			form.find('.rowClass').show(); //this is for field width
			form.find('.rowDescription2').hide();



		}  
		else if (type === 'number') {
			form.find('.rowLabel1').hide();
			form.find('.rowDescription2').hide();
			form.find('.rowOptions').hide();
			form.find('.rowMaxlength').show();
			form.find('.rowValidate').hide();
			form.find('.rowDescription2').hide();


		} 
		else {
			form.find('.rowOptions').hide();
			form.find('.rowCustomText').hide();
		}

		$('.accountdialog form .rowPricing').hide();

		setup_enhanced_multi_select(form);
	}



	function showAllFields(form) {
		form.find('.rowLabel').show();
		form.find('.rowOptions').show();
		form.find('.rowPlaceholder').show();
		form.find('.rowAccess').show();
		form.find('.rowRequired').show();
		form.find('.rowValidate').show();
		form.find('.rowExtoptions').hide();
		form.find('.rowTimepicker').hide();
		form.find('.rowPricing').show();
	}


	_selectAllCheckoutFields = function selectAllCheckoutFields(elm) {
		var checkAll = $(elm).prop('checked');
		$('#jwcfe_checkout_fields tbody input:checkbox[name=select_field]').prop('checked', checkAll);
	}



	function isHtmlIdValid(id) {
		var re = /^[a-zA-Z\_]+[a-z0-9\-_]*$/;
		return re.test(id.trim());
	}

	


	//===================== shorting & draged
	$(document).ready(function () {
		$('.jwcfe-opt-container').on('mousedown', '.sort', function (e) {
			var $draggedElement = $(this).closest('.jwcfe-opt-row');
			var $container = $draggedElement.closest('.jwcfe-opt-container');
			var startY = e.pageY;
			var startOffset = $draggedElement.offset().top;

			$(document).on('mousemove', function (e) {
				var moveY = e.pageY;
				var moveOffset = startOffset + (moveY - startY);
				var containerTop = $container.offset().top;
				var containerBottom = containerTop + $container.outerHeight() - $draggedElement.outerHeight();

				// Constrain the movement within the container
				if (moveOffset >= containerTop && moveOffset <= containerBottom) {
					$draggedElement.offset({ top: moveOffset });
				}
			});

			$(document).on('mouseup', function () {
				$(document).off('mousemove');
				$(document).off('mouseup');

				// Rearrange elements
				var newPosition = $draggedElement.offset().top;
				$container.children('.jwcfe-opt-row').each(function () {
					var $currentRow = $(this);
					if ($currentRow.is($draggedElement)) return;

					var currentTop = $currentRow.offset().top;
					if (newPosition < currentTop) {
						$draggedElement.insertBefore($currentRow);
						return true; // Stop looping once inserted
					}
				});
			});

			// Prevent text selection while dragging
			e.preventDefault();
		});
	});

	$(document).ready(function () {
		$(".jwcfe-opt-container").sortable({
			handle: ".sort",
			placeholder: "ui-state-highlight",
			tolerance: "pointer",
			start: function (event, ui) {
				ui.placeholder.height(ui.item.height());
				ui.placeholder.css({
					visibility: "visible",
					background: "#f0f0f0", // Style the placeholder as needed
					border: "1px dashed #ccc" // Example styling for placeholder
				});

				// Add initial margins for equal spacing
				$(".jwcfe-opt-row").css("margin-bottom", "0px");
				$(".jwcfe-opt-row:last-child").css("margin-bottom", "0");
			},
			sort: function (event, ui) {
				// Adjust margins dynamically during sorting
				$(".jwcfe-opt-row").css("margin-bottom", "0px");
				$(".jwcfe-opt-row:last-child").css("margin-bottom", "0");
			},
			stop: function (event, ui) {
				// Ensure all rows have equal spacing after sorting
				$(".jwcfe-opt-row").css("margin-bottom", "0px"); // Adjust the value as needed
				$(".jwcfe-opt-row:last-child").css("margin-bottom", "0"); // Remove bottom margin for the last item
			}
		}).disableSelection();
	});



	return {

		saveCustomFieldForm: _saveCustomFieldForm,

		openNewFieldForm: _openNewFieldForm,

		openEditFieldForm: _openEditFieldForm,

		removeSelectedFields: _removeSelectedFields,

		enableDisableSelectedFields: _enableDisableSelectedFields,

		fieldTypeChangeListner: _fieldTypeChangeListner,

		selectAllCheckoutFields: _selectAllCheckoutFields,

		addNewOptionRow: addNewOptionRow,

		removeOptionRow: removeOptionRow,


	};

}(window.jQuery, window, document));




function saveCustomFieldForm(loaderPath, donePath) {
	jwcfe_settings.saveCustomFieldForm(loaderPath, donePath);
}


function saveFieldForm(tabName, pluginPath) {
	jwcfe_settings.saveFieldForm(tabName, pluginPath);
}


function jwcfeFieldTypeChangeListner(elm) {
	jwcfe_settings.fieldTypeChangeListner(elm);
}



function jwcfeRuleOperandChangeListner(elm, loaderPath, donePath) {
	jwcfe_settings.RuleOperandChangeListner(elm, loaderPath, donePath);
}


function jwcfeRemoveRuleRow(elm) {
	jwcfe_settings.remove_rule_row(elm);
}


function jwcfeRemoveRuleRowAjax(elm) {
	jwcfe_settings.remove_rule_row_ajax(elm);
}

function openNewFieldForm(tabName) {
	jwcfe_settings.openNewFieldForm(tabName);
}



function openEditFieldForm(elm, rowId) {
	jwcfe_settings.openEditFieldForm(elm, rowId);
}



function removeSelectedFields() {
	jwcfe_settings.removeSelectedFields();
}



function enableSelectedFields() {
	jwcfe_settings.enableDisableSelectedFields(1);
}



function disableSelectedFields() {
	jwcfe_settings.enableDisableSelectedFields(0);
}



function jwcfeSelectAllCheckoutFields(elm) {
	jwcfe_settings.selectAllCheckoutFields(elm);
}



function jwcfeAddNewOptionRow(elm) {
	jwcfe_settings.addNewOptionRow(elm);
}


function jwcfeRemoveOptionRow(elm) {
	jwcfe_settings.removeOptionRow(elm);
}