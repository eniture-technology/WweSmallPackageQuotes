var wweSmDsFormId = "#wwesm-ds-form";
var wweSmDsEditFormData = '';

require([
        'jquery',
        'Magento_Ui/js/modal/modal',
        'Magento_Ui/js/modal/confirm',
        'domReady!',
    ],
    function($, modal, confirmation) {

        var addDsModal = $('#wwesm-ds-modal');
        var options = {
            type: 'popup',
            modalClass: 'wwesm-add-ds-modal',
            responsive: true,
            innerScroll: true,
            title: 'Drop Ship',
            closeText: 'Close',
            focus : wweSmDsFormId + ' #wwesm-ds-nickname',
            buttons: [{
                text: $.mage.__('Save'),
                class: 'en-btn save-ds-ds',
                click: function (data) {
                    var $this = this;
                    var form_data = wweSmGetFormData($, wweSmDsFormId);
                    var ajaxUrl = wweSmDsAjaxUrl + 'SaveDropship/';

                    if ($(wweSmDsFormId).valid() && wweSmDsZipMilesValid()) {
                        //If form data is unchanged then close the modal and show updated message
                        if (wweSmDsEditFormData !== '' && wweSmDsEditFormData === form_data) {
                            jQuery('.wwesm-ds-msg').text('Drop ship updated successfully.').show('slow');
                            wweSmScrollHideMsg(1, 'html,body', '.ds', '.wwesm-ds-msg');
                            addDsModal.modal('closeModal');
                        } else {
                            $.ajax({
                                url: ajaxUrl,
                                type: 'POST',
                                data: form_data,
                                showLoader: true,
                                success: function (data) {
                                    if (wweSmDropshipSaveResSettings(data)) {
                                        addDsModal.modal('closeModal');
                                    }
                                },
                                error: function (result) {
                                    console.log('no response !');
                                }
                            });
                        }
                    }
                }
            }],
            keyEventHandlers: {
                tabKey: function () {
                    return;
                },
                /**
                 * Escape key press handler,
                 * close modal window
                 */
                escapeKey: function () {
                    if (this.options.isOpen && this.modal.find(document.activeElement).length ||
                        this.options.isOpen && this.modal[0] === document.activeElement) {
                        this.closeModal();
                    }
                }
            },
            closed: function () {
                wweSmModalClose(wweSmDsFormId, '#ds-', $);
            }
        };


        $('body').on('click', '.wwesm-del-ds', function (event) {
            event.preventDefault();
            confirmation({
                title: 'Worldwide Express Small Package Quotes',
                content: 'Warning! If you delete this location, Drop ship location settings will be disabled against products.',
                actions: {
                    always: function () {},
                    confirm: function () {
                        var dataset = event.currentTarget.dataset;
                        wweSmDeleteDropship(dataset.id, wweSmDsAjaxUrl);
                    },
                    cancel: function () {}
                }
            });
            return false;
        });


        //Add DS
        $('#wwesm-add-ds-btn').on('click', function () {
            var popup = modal(options, addDsModal);
            addDsModal.modal('openModal');
        });

        //Edit DS
        $('body').on('click', '.wwesm-edit-ds', function () {
            var dsId = $(this).data("id");
            if (typeof dsId !== 'undefined') {
                wweSmEditDropship(dsId, wweSmDsAjaxUrl);
                setTimeout(function () {
                    var popup = modal(options, addDsModal);
                    addDsModal.modal('openModal');
                }, 500);
            }
        });

        //Add required to Local Delivery Fee if Local Delivery is enabled
        $(wweSmDsFormId + ' #ds-enable-local-delivery').on('change', function () {
            if ($(this).is(':checked')) {
                $(wweSmDsFormId + ' #ds-ld-fee').addClass('required');
            } else {
                $(wweSmDsFormId + ' #ds-ld-fee').removeClass('required');
            }
        });

        //Get data of Zip Code
        $(wweSmDsFormId + ' #wwesm-ds-zip').on('change', function () {
            var ajaxUrl = wweSmAjaxUrl + 'WweSmallOriginAddress/';
            $(wweSmDsFormId + ' #ds-city').val('');
            $(wweSmDsFormId + ' #ds-state').val('');
            $(wweSmDsFormId + ' #ds-country').val('');
            wweSmGetAddressFromZip(ajaxUrl, this, wweSmGetDsAddressResSettings);
            $(wweSmDsFormId).validation('clearError');
        });
    });

    /**
     * Set Address from zipCode
     * @param {type} data
     * @returns {Boolean}
     */
    function wweSmGetDsAddressResSettings(data){
        let id = wweSmDsFormId;
        if( data.country === 'US' || data.country === 'CA'){
            var oldNick = jQuery( '#wwesm-ds-nickname' ).val();
            var newNick = '';
            var zip     = jQuery( '#wwesm-ds-zip' ).val();
            if (data.postcode_localities === 1) {
                jQuery(id + ' .city-select' ).show();
                jQuery(id + ' #ds-actname' ).replaceWith( data.city_option );
                jQuery(id + ' .city-multiselect' ).replaceWith( data.city_option );
                jQuery(id).on('change', '.city-multiselect', function(){
                    var city = jQuery(this).val();
                    jQuery(id + ' #ds-city').val(city);
                    jQuery(id + ' #wwesm-ds-nickname' ).val(wweSmSetDsNickname(oldNick, zip, city));
                });
                jQuery(id + " #ds-city" ).val( data.first_city );
                jQuery(id + ' #ds-state' ).val( data.state );
                jQuery(id + ' #ds-country' ).val( data.country );
                jQuery(id + ' .city-input' ).hide();
                newNick = wweSmSetDsNickname(oldNick, zip, data.first_city);
            }else{
                jQuery(id + ' .city-input' ).show();
                jQuery(id + ' #wh-multi-city' ).removeAttr('value');
                jQuery(id + ' .city-select' ).hide();
                jQuery(id + ' #ds-city' ).val( data.city );
                jQuery(id + ' #ds-state' ).val( data.state );
                jQuery(id + ' #ds-country' ).val( data.country );
                newNick = wweSmSetDsNickname(oldNick, zip, data.city);
            }
            jQuery(id + ' #wwesm-ds-nickname' ).val(newNick);
        }else if (data.msg){
            jQuery('.wwesm-ds-er-msg').text(data.msg).show('slow');
            wweSmScrollHideMsg(2, '', '.wwesm-ds-er-msg', '.wwesm-ds-er-msg');
        }
        return true;
    }


    function wweSmDsZipMilesValid() {
        let  id = wweSmDsFormId;
        var enable_instore_pickup = jQuery(id + " #ds-enable-instore-pickup").is(':checked');
        var enable_local_delivery = jQuery(id + " #ds-enable-local-delivery").is(':checked');
        if (enable_instore_pickup || enable_local_delivery) {
            var instore_within_miles = jQuery(id + " #ds-within-miles").val();
            var instore_postal_code  = jQuery(id + " #ds-postcode-match").val();
            var ld_within_miles      = jQuery(id + " #ds-ld-within-miles").val();
            var ld_postal_code       = jQuery(id + " #ds-ld-postcode-match").val();

            switch(true){
                case (enable_instore_pickup && (instore_within_miles.length == 0 && instore_postal_code.length == 0)):
                    jQuery(id + ' .ds-instore-miles-postal-err').show('slow');
                    wweSmScrollHideMsg(2, '', id + ' #ds-is-heading-left', '.ds-instore-miles-postal-err');
                    return false;

                case (enable_local_delivery && (ld_within_miles.length == 0 && ld_postal_code.length == 0)):
                    jQuery(id + ' .ds-local-miles-postals-err').show('slow');
                    wweSmScrollHideMsg(2, '', id + ' #ds-ld-heading-left', '.ds-local-miles-postals-err');
                    return false;

            }
        }
        return true;
    }


    function wweSmDropshipSaveResSettings(data){
        if (data.insert_qry == 1) {
            jQuery('.wwesm-ds-msg').text(data.msg).show('slow');

            jQuery('#append-dropship tr:last').after(
                '<tr id="row_' + data.id + '" data-id="' + data.id+ '">' +
                '<td>'+data.nickname+'</td>' +
                wweSmGetRowData(data, 'ds') + '</tr>');

            wweSmScrollHideMsg(1, 'html,body', '.ds', '.wwesm-ds-msg');

        } else if(data.update_qry == 1) {
            jQuery('.wwesm-ds-msg').text(data.msg).show('slow');

            jQuery('tr[id=row_' + data.id + ']').html('<td>'+data.nickname+'</td>' + wweSmGetRowData(data, 'ds'));

            wweSmScrollHideMsg(1, 'html,body', '.ds', '.wwesm-ds-msg');

        }else{
            jQuery('.wwesm-ds-er-msg').text(data.msg).show('slow');
            wweSmScrollHideMsg(2, '', '.wwesm-ds-er-msg', '.wwesm-ds-er-msg');
            return false;
        }

        return true;
    }

    function wweSmEditDropship(dataId, ajaxUrl)
    {
        ajaxUrl = ajaxUrl + 'EditDropship/';
        var parameters = {
            'action'    : 'edit_dropship',
            'edit_id'   : dataId
        };

        wweSmAjaxRequest(parameters, ajaxUrl, wweSmDropshipEditResSettings);
        return false;
    }

    function wweSmDropshipEditResSettings(data){
        let id = wweSmDsFormId;
        if (data[0]) {
            jQuery(id + ' #ds-edit-form-id' ).val( data[0].warehouse_id );
            jQuery(id + ' #wwesm-ds-zip' ).val( data[0].zip );
            jQuery(id + ' #wwesm-ds-nickname' ).val( data[0].nickname );
            jQuery(id + ' .city-select' ).hide();
            jQuery(id + ' .city-input' ).show();
            jQuery(id + ' #ds-city' ).val( data[0].city );
            jQuery(id + ' #ds-state' ).val( data[0].state );
            jQuery(id + ' #ds-country' ).val( data[0].country );

            if (wweSmAdvancePlan) {
                // Load instore pickup and local delivery data
                if((data[0].in_store != null && data[0].in_store != 'null')
                    || (data[0].local_delivery != null && data[0].local_delivery != 'null')){
                    wweSmSetInspAndLdData(data[0], '#ds-');
                }
            }

            wweSmDsEditFormData = wweSmGetFormData(jQuery, wweSmDsFormId);
        }
        return true;
    }

    function wweSmDeleteDropship(deleteid, ajaxUrl)
    {
        ajaxUrl = ajaxUrl + 'DeleteDropship/';
        var parameters = {
            'action'    : 'delete_dropship',
            'delete_id' : deleteid
        };
        wweSmAjaxRequest(parameters, ajaxUrl, wweSmDropshipDeleteResSettings);

        return false;
    }

    function wweSmDropshipDeleteResSettings(data){
        if (data.qryResp == 1) {
            jQuery('#row_'+data.deleteID).remove();
            jQuery('.wwesm-ds-msg').text(data.msg).show('slow');
            wweSmScrollHideMsg(1, 'html,body', '.ds', '.wwesm-ds-msg');
        }
        return true;
    }

    function wweSmSetDsNickname(oldNick, zip, city) {
        var nickName = '';
        var curNick = 'DS_'+zip+'_'+city;
        var pattern = /DS_[0-9 a-z A-Z]+_[a-z A-Z]*/;
        var regex = new RegExp(pattern, 'g');
        if(oldNick !== ''){
            nickName =  regex.test(oldNick) ? curNick : oldNick;
        }
        return nickName;
    }
