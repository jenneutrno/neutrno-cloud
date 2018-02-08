
(function() {

    OCA.ImageSigning = OCA.ImageSigning || {};

    OCA.ImageSigning.AdminSettings = {

        dropdown : null,
        checkActive : null,
        txtName : null,
        txtWorker : null,
        txtUrl : null,
        btnUpdate : null,
        btnAdd : null,
        btnRemove : null,

        currentSignerIndex : null,
        context : null,



        init: function () {
            //// Set controls reference
            let ths = OCA.ImageSigning.AdminSettings;
            ths.dropdown = $('#ImageSigning select');
            ths.checkActive = $('#imagesigning_active');
            ths.txtName = $('#imagesigning_name');
            ths.txtWorker = $('#imagesigning_worker');
            ths.txtUrl = $('#imagesigning_url');
            ths.btnUpdate = $('#imagesigning_update');
            ths.btnAdd = $('#imagesigning_add');
            ths.btnRemove = $('#imagesigning_remove');

            //// Set controls trigger
            ths.dropdown.change(this.fillForm);
            ths.checkActive.change(this.setActive);
            ths.txtName.on('input',this.updateText);
            ths.txtWorker.on('input',this.updateText);
            ths.txtUrl.on('input',this.updateText);
            ths.btnUpdate.click(this.updateAction);
            ths.btnAdd.click(this.addAction);
            ths.btnRemove.click(this.removeAction);

            //// Get back context from server
            $.ajax({
                url: OC.generateUrl('/apps/imagesigning/getcontext'),
                type: 'GET'
            }).done(function (response) {
                ths.context = response;
                //// Set dropdown active item and fills other controls
                ths.dropdown.val(response['activeSigner']);
                ths.fillForm();
            }).fail(function (response, code) {
            });
        },

        fillForm: function () {
            let ths = OCA.ImageSigning.AdminSettings;
            if (ths.context != null) {
                let signer = ths.getCurrentSigner();
                //// Set checkbox state
                ths.checkActive.prop('checked', (ths.context['activeSigner'] === signer['id']));
                //// Set text box
                ths.txtName.val(signer['name']);
                ths.txtWorker.val(signer['workerId']);
                ths.txtUrl.val(signer['serverURL']);
                ths.stateRemove();
            }
        },

        getCurrentSigner : function () {
            let ths = OCA.ImageSigning.AdminSettings;
            if (ths.context != null) {
                let signers = ths.context['signers'];
                for (let i=0; i<signers.length; i++) {
                    if (signers[i]['id'] === ths.dropdown.val()) {
                        ths.currentSignerIndex = i;
                        return signers[i];
                    }
                }
            }
            return null;
        },

        setActive : function () {
            let ths = OCA.ImageSigning.AdminSettings;
            let signer = ths.context['signers'][ths.currentSignerIndex];
            ths.context['activeSigner'] = (ths.checkActive.is(':checked')) ? signer['id'] : '';
            ths.warnUpdate();
        },

        updateText : function (e) {
            let ths = OCA.ImageSigning.AdminSettings;
            if (ths.context != null) {
                let signer = ths.context['signers'][ths.currentSignerIndex];

                switch (e.currentTarget['id']) {
                    case ('imagesigning_name') :
                        let oldKey = signer['id'];
                        signer['name'] = e.currentTarget['value'];
                        signer['id'] = 'signer_' + ((e.currentTarget['value']).replace(/\s/g, '')).toLowerCase();
                        ths.updateDropdown(oldKey, signer['id'], signer['name']);
                        break;
                    case ('imagesigning_worker') : signer['workerId'] = e.currentTarget['value']; break;
                    case ('imagesigning_url') : signer['serverURL'] = e.currentTarget['value']; break;
                }
                ths.warnUpdate();
            }
        },

        updateDropdown : function (currentId, newId, newText) {
            let ths = OCA.ImageSigning.AdminSettings;
            ths.dropdown.find('option').each(function () {
                if (this.value === currentId) {
                    this.value = newId;
                    this.text = newText;
                    return false;
                }
            });
            ths.warnUpdate();
        },

        updateAction : function (e) {
            e.preventDefault();
            let ths = OCA.ImageSigning.AdminSettings;
            $.ajax({
                url: OC.generateUrl('/apps/imagesigning/updatecontext'),
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(ths.context)
            }).done(function (response) { ths.btnUpdate.removeClass('buttonWarn');
            }).fail(function (response, code) {
            });
        },

        addAction : function (e) {
            e.preventDefault();
            let ths = OCA.ImageSigning.AdminSettings;
            let randomId = 'signer_' + (Math.floor((Math.random() * 1000000) + 1).toString());
            let aVoid = {id : randomId, name :'', serverURL : '', workerId : ''};
            ths.context['signers'].push (aVoid);
            ths.dropdown.append($('<option>', { value : randomId, text : '' }));
            //// Set active dropdown item a refresh controls
            ths.dropdown.val(randomId);
            ths.fillForm();

            ths.warnUpdate();
        },

        removeAction : function (e) {
            e.preventDefault();
            let ths = OCA.ImageSigning.AdminSettings;

            S7Adialogs.confirm (t('imagesigning', 'Remove current item?'), t('imagesigning', 'ImageSigning'), function (res) {
                if (res) {
                    //// Remove item from dropdown
                    ths.dropdown.find('option[value="' + ths.dropdown.val() + '"]').remove();
                    //// Remove item from list
                    let signer = ths.context['signers'][ths.currentSignerIndex];
                    ths.context['signers'] = $.grep(ths.context['signers'], function(value) {
                        return value !== signer;
                    });
                    //// Update ini file and refresh form
                    ths.updateAction(e);
                    ths.fillForm();
                }
            }, true);
        },

        warnUpdate : function () {
            let ths = OCA.ImageSigning.AdminSettings;
            if (!ths.btnUpdate.hasClass('buttonWarn')) ths.btnUpdate.addClass('buttonWarn');
        },

        stateRemove : function () {
            let ths = OCA.ImageSigning.AdminSettings;
            ths.btnRemove.prop("disabled",(ths.dropdown.find('option').size() === 1));
        }

    };

})();


$(document).ready(function() {
    OCA.ImageSigning.AdminSettings.init();
});






