import OgImagePreview from './components/OgImagePreview.vue';

if (window.Statamic) {
    Statamic.booting(() => {
        Statamic.$components.register('og_image_preview-fieldtype', OgImagePreview);
    });
}
