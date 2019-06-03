function WpraGallery(config) {
    this.config = config;
    this.gallery = null;

    this.valueEl = null;
    this.openEl = null;
    this.removeEl = null;
    this.previewEl = null;

    config.elements && (this.valueEl = config.elements.value);
    config.elements && (this.openEl = config.elements.open);
    config.elements && (this.removeEl = config.elements.remove);
    config.elements && (this.previewEl = config.elements.preview);

    this.gallery = wp.media.frames.meta_gallery_frame = wp.media({
        title: config.title,
        button: config.button,
        library: config.library,
        multiple: config.multiple
    });

    // Hide the gallery side bar
    this.gallery.on('ready', function () {
        jQuery('.media-modal').addClass('no-sidebar');
    });

    // Set selected image when the gallery is opened
    this.gallery.on('open', function () {
        var id = this.valueEl.val();

        if (id) {
            var attachment = wp.media.attachment(id);
            attachment.fetch();
            this.gallery.state().get('selection').add(attachment ? [attachment] : []);
        }
    }.bind(this));

    // Update fields when an image is selected and the modal is closed
    this.gallery.on('select', function () {
        var image = this.gallery.state().get('selection').first();

        this.update({
            id: image.attributes.id,
            url: image.attributes.url,
        });
    }.bind(this));

    if (this.openEl !== null) {
        this.openEl.click(this.open.bind(this));
    }

    if (this.previewEl !== null) {
        this.previewEl.css({cursor: 'pointer'});
        this.previewEl.click(this.open.bind(this));
    }

    if (this.removeEl !== null) {
        this.removeEl.click(this.update.bind(this));
    }
}

WpraGallery.prototype.update = function (image) {
    if (image && image.id) {
        this.valueEl && this.valueEl.val(image.id);
        this.previewEl && this.previewEl.attr('src', image.url).show();
        this.removeEl && this.removeEl.show();
        this.openEl && this.openEl.hide();

        return;
    }

    this.valueEl && this.valueEl.val('');
    this.previewEl && this.previewEl.hide().attr('src', '');
    this.removeEl && this.removeEl.hide();
    this.openEl && this.openEl.show();
};

WpraGallery.prototype.open = function (image) {
    this.gallery.open();
};
