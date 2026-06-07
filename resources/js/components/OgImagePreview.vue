<template>
    <div class="og-image-preview">
        <div class="og-frame" :style="frameStyle">
            <iframe
                :src="previewSrc"
                :width="width"
                :height="height"
                :style="iframeStyle"
                scrolling="no"
            ></iframe>
        </div>

        <div v-if="entryId" class="og-actions">
            <button type="button" class="btn" :disabled="loading" @click="generate">
                {{ loading ? 'Generating…' : 'Generate now' }}
            </button>
            <span v-if="error" class="og-error">{{ error }}</span>
        </div>

        <p v-else class="og-muted">A sample preview. Open an entry to preview its own data and generate.</p>

        <div v-if="generatedUrl" class="og-result">
            <p class="og-result-label">Rendered by the API:</p>
            <img :src="generatedUrl" alt="Generated Open Graph image">
        </div>
    </div>
</template>

<script>
export default {
    props: {
        meta: { type: Object, default: () => ({}) },
    },

    data() {
        return {
            loading: false,
            error: null,
            generatedUrl: null,
        };
    },

    computed: {
        width() {
            return this.meta.width || 1200;
        },
        height() {
            return this.meta.height || 630;
        },
        scale() {
            return 540 / this.width;
        },
        entryId() {
            const match = window.location.pathname.match(/entries\/([^/]+)/);

            return match ? match[1] : null;
        },
        previewSrc() {
            const entry = this.entryId ? `entry=${encodeURIComponent(this.entryId)}&` : '';

            return `${this.meta.preview_url}?${entry}t=${Date.now()}`;
        },
        frameStyle() {
            return {
                width: '540px',
                height: `${Math.round(this.height * this.scale)}px`,
                overflow: 'hidden',
                borderRadius: '8px',
                border: '1px solid var(--c-border, #e2e8f0)',
                background: '#0b1120',
            };
        },
        iframeStyle() {
            return {
                transform: `scale(${this.scale})`,
                transformOrigin: 'top left',
                border: '0',
            };
        },
    },

    methods: {
        generate() {
            this.loading = true;
            this.error = null;

            const axios = this.$axios || window.Statamic.$axios;

            axios
                .post(this.meta.generate_url, { entry: this.entryId })
                .then((response) => {
                    this.generatedUrl = `${response.data.url}?t=${Date.now()}`;
                })
                .catch((e) => {
                    this.error = e.response?.data?.message || 'Generation failed. Check your API key and the logs.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>

<style>
.og-image-preview .og-actions { margin-top: 12px; display: flex; align-items: center; gap: 12px; }
.og-image-preview .og-error { color: #b91c1c; font-size: 13px; }
.og-image-preview .og-result { margin-top: 16px; }
.og-image-preview .og-result-label { font-size: 13px; color: #64748b; margin: 0 0 6px; }
.og-image-preview .og-result img { max-width: 540px; width: 100%; height: auto; border-radius: 8px; }
.og-image-preview .og-muted { color: #64748b; font-size: 14px; }
</style>
