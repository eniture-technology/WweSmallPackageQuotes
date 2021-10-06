/**
 * Document load function
 * @type type
 */

// require([ 'jquery', 'jquery/ui'], function($){
//     $(document).ready(function($) {
//         if($("#suspend-rad-use").length > 0 && $("#suspend-rad-use").is(":disabled") == false) {
//             disablealwaysresidentialwwe();
//             if (($('#suspend-rad-use:checkbox:checked').length)>0) {
//                 $("#quoteSetting_third_residentialDlvry").prop({disabled: false});
//             } else {
//                 $("#quoteSetting_third_residentialDlvry").val('0');
//                 $("#quoteSetting_third_residentialDlvry").prop({disabled: true});
//             }
//         }
//     });
//
//     /**
//     * windows onload
//     */
//     $(window).load(function(){
//         if($("#suspend-rad-use").length > 0 && $("#suspend-rad-use").is(":disabled") == false) {
//             if(!isdisabled){
//                 if (($('#suspend-rad-use:checkbox:checked').length)>0) {
//                    $("#quoteSetting_third_residentialDlvry").prop({disabled: false});
//                } else {
//                    $("#quoteSetting_third_residentialDlvry").val('0');
//                    $("#quoteSetting_third_residentialDlvry").prop({disabled: true});
//                }
//            }
//         }
//     });
// });
//
// /**
//  *
//  * @return {undefined}
//  */
// function disablealwaysresidentialwwe(){
//     jQuery("#suspend-rad-use").on('click', function ()
//     {
//         if (this.checked) {
//             jQuery("#quoteSetting_third_residentialDlvry").prop({disabled: false});
//         } else {
//             jQuery("#quoteSetting_third_residentialDlvry").val('0');
//             jQuery("#quoteSetting_third_residentialDlvry").prop({disabled: true});
//         }
//     });
// }

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