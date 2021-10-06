
    require([
        'jquery',
        'jquery/validate',
        'mage/translate',
        'domReady!'
    ], function($, validator){

        $('input').on('beforeItemAdd', function(event) {
            // Remove is there is already an error
            $(this).parent().find('.alphanum-error').remove();

            if (!wweSmValidateAlphaNumOnly(event.item)) {
                let error = '<label generated="true" class="mage-error alphanum-error">Please use only letters (a-z or A-Z) or numbers (0-9) in this field. No spaces or other characters are allowed.</label>';
                $(this).parent().append(error);
                event.cancel = true;
            }
        });

        $.validator.addMethod(
            'validate-wwesm-decimal-limit-2', function (value) {
                return (wweSmValidateDecimal($, value, 2)) ? true : false;
        }, $.mage.__('Maximum 2 digits allowed after decimal point.'));
        $.validator.addMethod(
            'validate-wwesm-decimal-limit-3', function (value) {
                return (wweSmValidateDecimal($,value,3)) ? true : false;
        }, $.mage.__('Maximum 3 digits allowed after decimal point.'));

        $.validator.addMethod(
            'validate-wwesm-integer', function (value) {
                return wweSmValidateInteger($,value);
        }, $.mage.__('It should be integer'));
    });


    /**
     * Get address against zipCode from smart street api
     * @param {type} ajaxUrl
     * @returns {Boolean}
     */
    function wweSmGetAddressFromZip(ajaxUrl, $this, callfunction) {
        var zipCode         = $this.value;
        var action          = jQuery($this).data('action');
        if (zipCode === '') {
            return false;
        }
        var parameters = {
            'action'      : action,
            'origin_zip'  : zipCode
        };

        wweSmAjaxRequest(parameters, ajaxUrl, callfunction);
    }

    /*
     * Hide message
     */
    function wweSmScrollHideMsg(scrollType, scrollEle, scrollTo, hideEle) {

        if (scrollType == 1){
            jQuery(scrollEle).animate({ scrollTop: jQuery(scrollTo).offset().top - 170 });
        }else if (scrollType == 2){
            jQuery(scrollTo)[0].scrollIntoView({behavior: "smooth"});
        }
        setTimeout(function () {
            jQuery(hideEle).hide('slow');
        }, 5000);
    }


    function wweSmValidateDecimal($ , value, limit){
        switch (limit) {
            case 4:
                var pattern=/^[+-]?\d*(\.\d{0,4})?$/;
                break;
            case 3:
                var pattern=/^[+-]?\d*(\.\d{0,3})?$/;
                break;
            default:
                var pattern=/^[+-]?\d*(\.\d{0,2})?$/;
                break;
        }
        var regex = new RegExp(pattern, 'g');
        return regex.test(value);
    }

    function wweSmValidateAlphaNumOnly(value){
        var pattern = /[a-zA-Z0-9]/;
        var regex = new RegExp(pattern, 'g');
        return regex.test(value);
        // value.val(value.val().replace(/[^a-z0-9]/g,''));
    }


    function wweSmCurrentPlanNote($, planMsg, carrierdiv){
        let wweDivAfter = '<div class="message message-notice notice wwesm-plan-note">' + planMsg + '</div>';
        wweSmNotesToggleHandling($, wweDivAfter, '.wwesm-plan-note', carrierdiv);
    }

    /**
     *
     * @param $
     * @param {string} wweDivAfter
     * @param {string} className
     * @param {string} carrierDiv
     */
    function wweSmNotesToggleHandling($, wweDivAfter, className, carrierDiv){

        setTimeout(function () {
            if($(carrierDiv).attr('class') === 'open'){
                $(carrierDiv).after(wweDivAfter);
            }
        },1000);

        $(carrierDiv).click(function(){
            if($(carrierDiv).attr('class') === 'open'){
                $(carrierDiv).after(wweDivAfter);
            }else if($(className).length){
                $(className).remove();
            }
        });
    }


    /**
     * @param canAddWh
     */
    function wweSmAddWarehouseRestriction(canAddWh){
        let appendWh = jQuery("#append-warehouse");
        let addWhBtn = jQuery("#wwesm-add-wh-btn");
        let planMsg  = '<a href="https://eniture.com/magento2-worldwide-express-small-package/" target="_blank" class="required-plan-msg">Standard Plan required</a>';

        switch(canAddWh)
        {
            case 0:
                appendWh.find("tr").removeClass('inactiveLink');
                addWhBtn.addClass('inactiveLink');
                if (jQuery(".required-plan-msg").length === 0) {
                    addWhBtn.after(planMsg);
                }
                appendWh.find("tr:gt(1)").addClass('inactiveLink');
                break;

            case 1:
                addWhBtn.removeClass('inactiveLink');
                jQuery('.required-plan-msg').remove();
                appendWh.find("tr").removeClass('inactiveLink');
                break;

            default:
                break;
        }

    }
    /**
     * Call for ajax requests
     * @param {object} parameters
     * @param {string} ajaxUrl
     * @param {function} responseFunction
     * @returns {function}
     */
    function wweSmAjaxRequest(parameters, ajaxUrl, responseFunction){
        new Ajax.Request(ajaxUrl, {
            method:  'POST',
            parameters: parameters,
            onSuccess: function(response){
                var json = response.responseText;
                var data = JSON.parse(json);
                var callbackRes = responseFunction(data);
                return callbackRes;

            }
        });
    }


    /**
     * Restrict Quote Settings Fields
     * @param {object} qRestriction
     */
    function wweSmPlanQuoteRestriction(qRestriction){
        let msgFirstPart = '<tr><td><label><span data-config-scope=""></span></label></td><td class="value"><a href="https://eniture.com/magento2-worldwide-express-small-package/" target="_blank" class="required-plan-msg';
        let msgLastPart = ' Plan required</a></td><td class=""></td></tr>';
        let quoteSecRowID = "#row_WweSmQuoteSetting_third_";
        let quoteSecID = "#WweSmQuoteSetting_third_";
        let parsedData = JSON.parse(qRestriction)
        if(parsedData['advance']){
            jQuery(''+quoteSecRowID+'transitDaysNumber').before( msgFirstPart + '"adv-plan-err">Advanced' + msgLastPart);
            wweSmDisabledFieldsLoop(parsedData['advance'], quoteSecID);
        }

        if(parsedData['standard']){
            jQuery(''+quoteSecRowID+'onlyGndService').before(msgFirstPart + '" std-plan-err">Standard' + msgLastPart);
            wweSmDisabledFieldsLoop(parsedData['standard'], quoteSecID);
        }
    }

    /**
     * @param dataArr
     * @param quoteSecID
     */
    function wweSmDisabledFieldsLoop(dataArr, quoteSecID){
        jQuery.each(dataArr, function( index, value ) {
            jQuery(quoteSecID + value).attr('disabled','disabled');
        });
    }

    /**
     * @param data
     * @param eleId
     */
    function wweSmSetInspAndLdData(data, eleid){
        try{
            var instore = JSON.parse(data.in_store);
            var localdel= JSON.parse(data.local_delivery);

            //Filling form data
            if(instore != null) {
                instore.enable_store_pickup == 1 ? jQuery(eleid + 'enable-instore-pickup').prop('checked', true) : '';
                jQuery(eleid + 'within-miles').val(instore.miles_store_pickup);
                jQuery(eleid + 'postcode-match').tagsinput('add', instore.match_postal_store_pickup);
                jQuery(eleid + 'checkout-descp').val(instore.checkout_desc_store_pickup);
                instore.suppress_other == 1 ? jQuery(eleid + 'ld-sup-rates').prop('checked', true) : '';
            }

            if(localdel != null) {
                if (localdel.enable_local_delivery == 1) {
                    jQuery(eleid + 'enable-local-delivery').prop('checked', true);
                    jQuery(eleid + 'ld-fee').addClass('required');
                }
                jQuery(eleid + 'ld-within-miles').val(localdel.miles_local_delivery);
                jQuery(eleid + 'ld-postcode-match').tagsinput('add', localdel.match_postal_local_delivery);
                jQuery(eleid + 'ld-checkout-descp').val(localdel.checkout_desc_local_delivery);
                jQuery(eleid + 'ld-fee').val(localdel.fee_local_delivery);
                localdel.suppress_other == 1 ? jQuery(eleid + 'ld-sup-rates').prop('checked', true) : '';
            }

        }catch (e) {
            return '';
        }

    }


    /**
     * @param {string} elemId
     * @param {string} msgClass
     * @param {string} msg
     */

    function wweSmResponseMessage(elemId, msgClass, msg) {

        let finalClass = 'message message-';

        switch (msgClass) {
            case 'success':
                finalClass += 'success success';
                break;
            case 'info':
                finalClass += 'info info';
                break;
            case 'error':
                finalClass += 'error error';
                break;
            default:
                finalClass += 'warning warning';
                break;
        }

        jQuery(elemId).addClass(finalClass);
        jQuery(elemId).text(msg).show('slow');

        setTimeout(function () {
            jQuery(elemId).hide('slow');
            jQuery(elemId).removeClass(finalClass);
        }, 5000);
    }

    function wweSmGetRowData(data, loc) {
        return '<td>' + data.origin_city + '</td>' +
            '<td>' + data.origin_state + '</td>' +
            '<td>' + data.origin_zip + '</td>' +
            '<td>' + data.origin_country + '</td>' +
            '<td><a href="javascript:;" data-id="' + data.id + '" title="Edit" class="wwesm-edit-'+loc+'">Edit</a>' +
            ' | ' +
            '<a href="javascript:;" data-id="' + data.id + '" title="Delete" class="wwesm-del-'+loc+'">Delete</a>' +
            '</td>';
    }

    //This function serialize complete form data
    function wweSmGetFormData($, formId) {
        // To initialize the Disabled inputs
        var disabled = $(formId).find(':input:disabled').removeAttr('disabled');
        var formData = $(formId).serialize();
        disabled.attr('disabled','disabled');
        var addData = '';
        $(formId + ' input[type=checkbox]').each(function() {
            if (!$(this).is(":checked")) {
                addData += '&' + $(this).attr('name') + '=';
            }
        });
        return formData+addData;
    }

    function wweSmModalClose(formId, ele, $) {
        $(formId).trigger("reset");
        $(formId).validation('clearError');
        $(formId + " .alphanum-error").remove();
        $(formId + ' ' + ele+'ld-fee').removeClass('required');
        $($(formId + " .bootstrap-tagsinput").find("span[data-role=remove]")).trigger("click");
        $(ele+'edit-form-id').val('');
        $('.city-select').hide();
        $('.city-input').show();
    }

    function wweSmValidateInteger($ , value){
        if((value % 1) === 0) {
            return true;
        }else{
            return false;
        }
    }