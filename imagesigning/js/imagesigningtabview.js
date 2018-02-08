
/* @global Handlebars */

(function() {

	var TEMPLATE =
		'<ul class="imagesigningcontainer"></ul>' +
		'<div class="clear-float"></div>' +
		'<div class="empty hidden">' +
		'<div class="emptycontent">' +
		'<div class="icon-history"></div>' +
		'</div></div>' +
        '<img class="waiting-circle" src="'+OC.imagePath('imagesigning', 'ico-loading.gif')+'" style="display:none;"/>' +
		'<input id="signfile-action-button" type="button" class="signfile-button" value="{{ buttonLabel }}" />' +
        '<h4 id="signfile-underneath-message">{{ underneathMsg }}</h4>';

	/**
	 * @memberof OCA.ImageSigning
	 */
	var ImageSigningTabView = OCA.Files.DetailTabView.extend(
		/** @lends OCA.ImageSigning.ImageSigningTabView.prototype */ {
		id: 'imagesigningTabView',
		className: 'tab imagesigningTabView',
        context: null,
		_template: null,
		$imagesigningContainer: null,
        abortSignFileAction: false,
        currentAjaxSigning: null,
		events: {
			'click .signfile-button': '_onClickSignFile'
		},

		initialize: function() {
			OCA.Files.DetailTabView.prototype.initialize.apply(this, arguments);
			this.collection = new OCA.ImageSigning.ImageSigningCollection();
			this.collection.on('request', this._onRequest, this);
			this.collection.on('sync', this._onEndRequest, this);
			this.collection.on('update', this._onUpdate, this);
			this.collection.on('error', this._onError, this);
			this.collection.on('add', this._onAddModel, this);

            $.ajax({
                url: OC.generateUrl('/apps/imagesigning/getcontext'),
                type: 'GET'
            }).done(function (response) { ImageSigningTabView.context = response;
            }).fail(function (response, code) {
            });
		},

		getLabel: function() {
			return t('imagesigning', 'Code Signing');
		},

		nextPage: function() {
			if (this._loading || !this.collection.hasMoreResults()) {
				return;
			}

			if (this.collection.getFileInfo() && this.collection.getFileInfo().isDirectory()) {
				return;
			}
			this.collection.fetchNext();
		},

		_onClickSignFile: function(e) {
			e.preventDefault();

            var fileInfo = this.collection.getFileInfo();
            var baseDir = fileInfo.attributes.path;


            S7Adialogs.filepicker(t('files', 'Target folder'), function(targetDir) {
                var fileName = fileInfo.attributes.name;
                var actionUrl = OC.generateUrl('/apps/imagesigning/crypt/cryptfile');
                var abortUrl = OC.generateUrl('/apps/imagesigning/crypt/cryptabort');
                var data = {
                      dir : baseDir
                    , fname: fileName
                    , targetdir : targetDir
                };

                var waitingId = S7Adialogs.waiting ("Signing in progress ...", t("imagesigning","Sign File"), function() {
                    ImageSigningTabView.abortSignFileAction = true;
                    ImageSigningTabView.currentAjaxSigning.abort();
                    S7Adialogs.closeDiag(waitingId);
                    $.ajax({
                        url: abortUrl,
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(data)
                    }).done(function (response) {
                    }).fail(function (response, code) {
                    });
                }, true);

                ImageSigningTabView.currentAjaxSigning = $.ajax({
                    url: actionUrl,
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data)
                }).done(function (response) {
                    S7Adialogs.closeDiag(waitingId);
                    if (!ImageSigningTabView.abortSignFileAction) {
                        if (response['res'] >= 0) {
                            if (response['res'] === 0) {
                                var currentDir = OCA.Files.App.fileList.getCurrentDirectory ();
                                if (currentDir === targetDir) OCA.Files.App.fileList.addAndFetchFileInfo (response['prefix'] + fileName, targetDir);
                            }
                            else {
                                OC.dialogs.alert(t('imagesigning', response['errormsg']), t('imagesigning', 'Sign file error'), undefined, true);
                            }
                        }
                    }
                    else ImageSigningTabView.abortSignFileAction = false;
                }).fail(function (response, code) {
                    if (!ImageSigningTabView.abortSignFileAction) {
                        S7Adialogs.closeDiag(waitingId);
                        OC.dialogs.alert(t('imagesigning', response['errormsg']), t('imagesigning', 'Sign file error'), undefined, true);
                    }
                    else ImageSigningTabView.abortSignFileAction = false;
                });

            }, false, "httpd/unix-directory", true, baseDir);
		},

		_onRequest: function() {
		},

		_onEndRequest: function() {
		},

		_onAddModel: function(model) {
		},

		template: function(data) {
			if (!this._template) {
				this._template = Handlebars.compile(TEMPLATE);
			}

			return this._template(data);
		},

		setFileInfo: function(fileInfo) {
			if (fileInfo) {
                let notMime = ImageSigningTabView.context['allowMime'].indexOf(fileInfo.attributes['mimetype']) < 0;
                let signed = (fileInfo.attributes['name']).lastIndexOf(ImageSigningTabView.context['signedFilePrefix'], 0) === 0;
                let underneathText = '';
                if (signed) underneathText = t('imagesigning', 'This file has been digitally signed');
                else if (notMime) underneathText = t('imagesigning', 'Currently, digital Signing is not available for this file type');

				this.render(underneathText);
				this.collection.setFileInfo(fileInfo);
				this.collection.reset([], {silent: true});
				this.nextPage();

                if (notMime || signed) {
                    $("#signfile-action-button").prop("disabled",true);
                    $("#signfile-underneath-message").css("visibility", "visible");
                }
			} else {
				this.render();
				this.collection.reset();
			}
		},

		/**
		 * Renders this details view
		 */
		render: function(underneathText) {
			this.$el.html(this.template({
				buttonLabel: t('imagesigning', 'Sign File'),
                underneathMsg: underneathText
			}));
			this.$el.find('.has-tooltip').tooltip();
			this.$imagesigningContainer = this.$el.find('imagesigningcontainer');
			this.delegateEvents();
		},

		/**
		 * Returns true for files, false for folders.
		 *
		 * @return {bool} true for files, false for folders
		 */
		canDisplay: function(fileInfo) {
			if (!fileInfo) {
				return false;
			}
			return !fileInfo.isDirectory();
		}

	});

	OCA.ImageSigning = OCA.ImageSigning || {};

	OCA.ImageSigning.ImageSigningTabView = ImageSigningTabView;
})();
