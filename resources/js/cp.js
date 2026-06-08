import OgImagePreview from './components/OgImagePreview.vue';
import OgImageStatus from './components/OgImageStatus.vue';

if (window.Statamic) {
    Statamic.booting(() => {
        Statamic.$components.register('og_image_preview-fieldtype', OgImagePreview);
        Statamic.$components.register('og_image_status-fieldtype', OgImageStatus);
    });
}
