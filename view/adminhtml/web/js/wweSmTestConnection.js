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
                    'clientId' : 'Client ID',
                    'clientSecret' : 'Client Secret',
                    'usernameNewAPI' : 'Username',
                    'passwordNewAPI' : 'Password',
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
        let apiEndpoint = $(sectionId+'apiEndpoint').val();
        let credentials = {
            'pluginLicenceKey'    : $(sectionId+'licenseKey').val(),
            'apiEndpoint'         : apiEndpoint
        };

        if(apiEndpoint == 'new'){
            credentials.clientId            = $(sectionId+'clientId').val();
            credentials.clientSecret        = $(sectionId+'clientSecret').val();
            credentials.username            = $(sectionId+'usernameNewAPI').val();
            credentials.password            = $(sectionId+'passwordNewAPI').val();
            
        }else{
            credentials.accountNumber       = $(sectionId+'accountNumber').val();
            credentials.username            = $(sectionId+'username').val();
            credentials.password            = $(sectionId+'password').val();
            credentials.authenticationKey   = $(sectionId+'authenticationKey').val();
        }

        wweSmAjaxRequest(credentials, ajaxURL, wweSmTestConnResponse);
        
    }
    
    /**
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

    /**
     * Test connection ajax call
     * @param {object} $
     * @param {string} ajaxURL
     * @returns {function}
     */
    function wweSmPlanRefresh(e){
        let ajaxURL = e.getAttribute('planRefAjaxUrl');
        let parameters = {};
        wweSmAjaxRequest(parameters, ajaxURL, wweSmPlanRefreshResponse);
    }

    /**
     * Handle response
     * @param {object} data
     * @returns {void}
     */
    function wweSmPlanRefreshResponse(data){
        document.location.reload(true);
    }
