    require(["jquery", "domReady!"], function ($) {
        /* Test Connection Validation */
        wweSmAddTestConnTitle($);
        $('#wwesm-test-conn').click(function () {
            if ($('#config-edit-form').valid()) {
                let ajaxURL = $(this).attr('connAjaxUrl');
                wweSmTestConnAjaxCall($, ajaxURL);
            }
        return false;
        });
    });
    
    /**
     * Assign Title to inputs
     */
    function wweSmAddTestConnTitle($)
    {
        let sectionId = '#WweSmConnSetting_first_';
        let data = {'accountNumber' : 'Account Number',
                    'username' : 'Username',
                    'password' : 'Password',
                    'authenticationKey' : 'Authentication Key',
                    'licenseKey' : 'Plugin License Key'
                    };

        for (let id in data) {
            let title = data[id];
            $(sectionId+id).attr('title', title);
        }
    }
    
    /**
     * Test connection ajax call
     * @param {object} $
     * @param {string} ajaxURL
     * @returns {function}
     */
    function wweSmTestConnAjaxCall($, ajaxURL){
        let sectionId = '#WweSmConnSetting_first_';
        let credentials = {
            accountNumber       : $(sectionId+'accountNumber').val(),
            username            : $(sectionId+'username').val(),
            password            : $(sectionId+'password').val(),
            authenticationKey   : $(sectionId+'authenticationKey').val(),
            pluginLicenceKey    : $(sectionId+'licenseKey').val()
        };

        wweSmAjaxRequest(credentials, ajaxURL, wweSmTestConnResponse);
        
    }
    
    /**
     * 
     * @param {object} data
     * @returns {void}
     */
    function wweSmTestConnResponse(data){
        let elemId = '#wwesm-conn-response';
        let msgClass, msgText =  '';
        if (data.Error) {
            msgClass = 'error';
            msgText = data.Error;
        } else {
            msgClass = 'success';
            msgText = data.Success;
        }
        wweSmResponseMessage(elemId, msgClass, msgText);
    }