
(function() {
	OCA.ImageSigning = OCA.ImageSigning || {};

	/**
	 * @namespace
	 */
	OCA.ImageSigning.Util = {
		/**
		 * Initialize the imagesigning plugin.
		 *
		 * @param {OCA.Files.FileList} fileList file list to be extended
		 */
		attach: function(fileList) {
            if (fileList.id === 'trashbin' || fileList.id === 'files.public') { return; }

			fileList.registerTabView(new OCA.ImageSigning.ImageSigningTabView('imagesigningTabView', {order: 12}));
		}
	};
})();

OC.Plugins.register('OCA.Files.FileList', OCA.ImageSigning.Util);

