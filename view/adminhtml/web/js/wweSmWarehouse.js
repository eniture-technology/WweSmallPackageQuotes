    var wweSmWhFormId = "#wwesm-wh-form";
    var wweSmWhEditFormData = '';
    require(
        [
            'jquery',
            'Magento_Ui/js/modal/modal',
            'domReady!'
        ],
        function($, modal) {

            let addWhModal = $('#wwesm-wh-modal');
            let formId = wweSmWhFormId;
            let options = {
                type: 'popup',
                modalClass: 'wwesm-add-wh-modal',
                responsive: true,
                innerScroll: true,
                title: 'Warehouse',
                closeText: 'Close',
                focus : formId + ' #wwesm-wh-zip',
                buttons: [{
                    text: $.mage.__('Save'),
                    class: 'en-btn save-wh-ds',
                    click: function (data) {
                        var $this = this;
                        var formData = wweSmGetFormData($, formId);
                        var ajaxUrl = wweSmAjaxUrl + 'SaveWarehouse/';

                        if ($(formId).valid() && wweSmZipMilesValid()) {
                            //If form data is unchanged then close the modal and show updated message
                            if (wweSmWhEditFormData !== '' && wweSmWhEditFormData === formData) {
                                jQuery('.wwesm-wh-msg').text('Warehouse updated successfully.').show('slow');
                                wweSmScrollHideMsg(1, 'html,body', '.wh-text', '.wwesm-wh-msg');
                                addWhModal.modal('closeModal');
                            } else {
                                $.ajax({
                                    url: ajaxUrl,
                                    type: 'POST',
                                    data: formData,
                                    showLoader: true,
                                    success: function (data) {
                                        if (wweSmWarehouseSaveResSettings(data)) {
                                            addWhModal.modal('closeModal');
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
                    tabKey: function () { },
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
                    wweSmModalClose(formId, '#', $);
                }
            };

            //Add WH
            $('#wwesm-add-wh-btn').on('click', function () {
                var popup = modal(options, addWhModal);
                addWhModal.modal('openModal');
            });

            //Edit WH
            $('body').on('click', '.wwesm-edit-wh', function () {
                var whId = $(this).data("id");
                if (typeof whId !== 'undefined') {
                    wweSmEditWarehouse(whId, wweSmAjaxUrl);
                    setTimeout(function () {
                        var popup = modal(options, addWhModal);
                        addWhModal.modal('openModal');
                    }, 500);
                }
            });

            //Delete WH
            $('body').on('click', '.wwesm-del-wh', function () {
                var whId = $(this).data("id");
                if (typeof whId !== 'undefined') {
                    wweSmDeleteWarehouse(whId, wweSmAjaxUrl);
                }
            });

            //Add required to Local Delivery Fee if Local Delivery is enabled
            $(formId + ' #enable-local-delivery').on('change', function () {
                if ($(this).is(':checked')) {
                    $(formId + ' #ld-fee').addClass('required');
                } else {
                    $(formId + ' #ld-fee').removeClass('required');
                }
            });

            //Get data of Zip Code
            $(formId + ' #wwesm-wh-zip').on('change', function () {
                let ajaxUrl = wweSmAjaxUrl + 'WweSmallOriginAddress/';
                $(formId + ' #wh-origin-city').val('');
                $(formId + ' #wh-origin-state').val('');
                $(formId + ' #wh-origin-country').val('');
                wweSmGetAddressFromZip(ajaxUrl, this, wweSmGetAddressResSettings);
                $(formId).validation('clearError');
            });
        }
    );


    function wweSmGetAddressResSettings(data){
        let id = wweSmWhFormId;
        if( data.country === 'US' || data.country === 'CA') {
            if (data.postcode_localities === 1) {
                jQuery(id+' .city-select').show();
                jQuery(id+' #actname').replaceWith(data.city_option);
                jQuery(id+' .city-multiselect').replaceWith(data.city_option);
                jQuery(id).on('change', '.city-multiselect',function () {
                    var city = jQuery(this).val();
                    jQuery(id+' #wh-origin-city').val(city);
                });
                jQuery(id+" #wh-origin-city").val(data.first_city);
                jQuery(id+" #wh-origin-state").val(data.state);
                jQuery(id+" #wh-origin-country").val(data.country);
                jQuery(id+' .city-input').hide();
            } else {
                jQuery(id+' .city-input').show();
                jQuery(id+' #wh-multi-city').removeAttr('value');
                jQuery(id+' .city-select').hide();
                jQuery(id+" #wh-origin-city").val(data.city);
                jQuery(id+" #wh-origin-state").val(data.state);
                jQuery(id+" #wh-origin-country").val(data.country);
            }
        }else if (data.msg){
            jQuery(id+' .wwesm-wh-er-msg').text(data.msg).show('slow');
            wweSmScrollHideMsg(2, '', '.wwesm-wh-er-msg', '.wwesm-wh-er-msg');
        }
        return true;
    }


    function wweSmZipMilesValid() {
        let id = wweSmWhFormId;
        var enable_instore_pickup = jQuery(id + " #enable-instore-pickup").is(':checked');
        var enable_local_delivery = jQuery(id + " #enable-local-delivery").is(':checked');
        if (enable_instore_pickup || enable_local_delivery) {
            var instore_within_miles = jQuery(id + " #within-miles").val();
            var instore_postal_code  = jQuery(id + " #postcode-match").val();
            var ld_within_miles      = jQuery(id + " #ld-within-miles").val();
            var ld_postal_code       = jQuery(id + " #ld-postcode-match").val();

            switch (true) {
                case (enable_instore_pickup && (instore_within_miles.length == 0 && instore_postal_code.length == 0)):
                    jQuery(id +' .wh-instore-miles-postal-err').show('slow');
                    wweSmScrollHideMsg(2, '', id + ' #wh-is-heading-left', '.wh-instore-miles-postal-err');
                    return false;

                case (enable_local_delivery && (ld_within_miles.length == 0 && ld_postal_code.length == 0)):
                    jQuery(id + ' .wh-local-miles-postals-err').show('slow');
                    wweSmScrollHideMsg(2, '', id + ' #wh-ld-heading-left', '.wh-local-miles-postals-err');
                    return false;
            }
        }
        return true;
    }

    function wweSmWarehouseSaveResSettings(data) {
        wweSmAddWarehouseRestriction(data.canAddWh);

        if (data.insert_qry == 1) {
            jQuery('.wwesm-wh-msg').text(data.msg).show('slow');

            jQuery('#append-warehouse tr:last').after(
                '<tr id="row_' + data.id + '" data-id="' + data.id+ '">' + wweSmGetRowData(data, 'wh') + '</tr>');

            wweSmScrollHideMsg(1, 'html,body', '.wh-text', '.wwesm-wh-msg');

        } else if (data.update_qry == 1) {
            jQuery('.wwesm-wh-msg').text(data.msg).show('slow');

            jQuery('tr[id=row_' + data.id + ']').html(wweSmGetRowData(data, 'wh'));

            wweSmScrollHideMsg(1, 'html,body', '.wh-text', '.wwesm-wh-msg');
        } else {
            jQuery('.wwesm-wh-er-msg').text(data.msg).show('slow');
            //to be changed
            wweSmScrollHideMsg(2, '', '.wwesm-wh-er-msg', '.wwesm-wh-er-msg');
            return false;
        }
        return true;
    }

    /**
     * Edit warehouse
     * @param {type} dataId
     * @param {type} ajaxUrl
     * @returns {Boolean}
     */
    function wweSmEditWarehouse(dataId, ajaxUrl) {
        ajaxUrl = ajaxUrl + 'EditWarehouse/';
        var parameters = {
            'action': 'edit_warehouse',
            'edit_id': dataId
        };

        wweSmAjaxRequest(parameters, ajaxUrl, wweSmWarehouseEditResSettings);
        return false;
    }

    function wweSmWarehouseEditResSettings(data) {
        let id = wweSmWhFormId;
        if (data[0]) {
            jQuery(id+' #edit-form-id').val(data[0].warehouse_id);
            jQuery(id+' #wwesm-wh-zip').val(data[0].zip);
            jQuery(id+' .city-select').hide();
            jQuery(id+' .city-input').show();
            jQuery(id+' #wh-origin-city').val(data[0].city);
            jQuery(id+' #wh-origin-state').val(data[0].state);
            jQuery(id+' #wh-origin-country').val(data[0].country);

            if (wweSmAdvancePlan) {
                // Load instorepikup and local delivery data
                if ((data[0].in_store != null && data[0].in_store != 'null')
                    || (data[0].local_delivery != null && data[0].local_delivery != 'null')) {
                    wweSmSetInspAndLdData(data[0], '#');
                }
            }
            wweSmWhEditFormData = wweSmGetFormData(jQuery, wweSmWhFormId);
        }
        return true;
    }

    /**
     * Delete selected Warehouse
     * @param {int} dataId
     * @param {string} ajaxUrl
     * @returns {boolean}
     */
    function wweSmDeleteWarehouse(dataId, ajaxUrl) {
        ajaxUrl = ajaxUrl + 'DeleteWarehouse/';
        var parameters = {
            'action': 'delete_warehouse',
            'delete_id': dataId
        };
        wweSmAjaxRequest(parameters, ajaxUrl, wweSmWarehouseDeleteResSettings);
        return false;
    }

    function wweSmWarehouseDeleteResSettings(data) {

        if (data.qryResp == 1) {
            jQuery('#row_' + data.deleteID).remove();
            wweSmAddWarehouseRestriction(data.canAddWh);
            jQuery('.wwesm-wh-msg').text(data.msg).show('slow');
            wweSmScrollHideMsg(1, 'html,body', '.wh-text', '.wwesm-wh-msg');
        }
        return true;
    }
