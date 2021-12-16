/**
 * Document load function
 * @type type
 */
require([
        'jquery',
        'domReady!'
    ],
    function($){

        let id = '#WweSmQuoteSetting_third_';
        if($("#suspend-rad-use").length > 0 && $("#suspend-rad-use").is(":disabled") == false) {
            if (($('#suspend-rad-use:checkbox:checked').length)>0) {
                $(id + "residentialDlvry").prop({disabled: false});
            } else {
                $(id + "residentialDlvry").val('0').prop({disabled: true});
            }
        }

        $("#suspend-rad-use").on('click', function () {
            if (this.checked) {
                $(id + "residentialDlvry").prop({disabled: false});
            } else {
                $(id + "residentialDlvry").val('0').prop({disabled: true});
            }
        });
    });