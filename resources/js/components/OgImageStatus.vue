<template>
    <!-- Missing key: a loud, unmissable warning with a direct call to action. -->
    <div v-if="!hasApiKey" class="og-status og-status--warning">
        <svg class="og-status__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.515 2.625H3.72c-1.345 0-2.188-1.458-1.515-2.625L8.485 2.495ZM10 6a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 6Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
        </svg>
        <div class="og-status__body">
            <p class="og-status__title">An API key is required</p>
            <p class="og-status__text">
                Open Graph images won’t be generated until you connect an html2img API key.
                Creating one is free.
            </p>
            <p class="og-status__text og-status__hint">
                Add it to your <code>.env</code> as <code>HTML2IMG_API_KEY</code>{{ keyHint }}.
            </p>
            <a class="og-status__cta" :href="registerUrl" target="_blank" rel="noopener">
                Get a free API key
                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 0 0 1.06 0l7.22-7.22v3.69a.75.75 0 0 0 1.5 0V5.5a.75.75 0 0 0-.75-.75H9.25a.75.75 0 0 0 0 1.5h3.69l-7.22 7.22a.75.75 0 0 0 0 1.31Z" clip-rule="evenodd" /></svg>
            </a>
        </div>
    </div>

    <!-- Key present on the settings screen: a quiet confirmation. -->
    <div v-else-if="onSettingsPage" class="og-status og-status--ok">
        <svg class="og-status__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M16.704 5.29a.75.75 0 0 1 .006 1.06l-7.5 7.6a.75.75 0 0 1-1.07.005l-3.75-3.75a.75.75 0 1 1 1.06-1.06l3.213 3.212 6.97-7.06a.75.75 0 0 1 1.06-.006Z" clip-rule="evenodd" />
        </svg>
        <div class="og-status__body">
            <p class="og-status__title">API key connected</p>
            <p class="og-status__text">
                Open Graph images will be generated automatically when entries in enabled
                collections are saved.
            </p>
        </div>
    </div>
    <!-- Key present on an entry: render nothing, the tab stays clean. -->
</template>

<script>
export default {
    props: {
        meta: { type: Object, default: () => ({}) },
    },

    computed: {
        hasApiKey() {
            return !!this.meta.has_api_key;
        },
        registerUrl() {
            return this.meta.register_url || 'https://app.html2img.com/register';
        },
        onSettingsPage() {
            return window.location.pathname.includes('/addons/');
        },
        keyHint() {
            return this.onSettingsPage
                ? ', or paste it in the API key field below'
                : ', or paste it under Tools → Open Graph Images';
        },
    },
};
</script>

<style>
/* The banner carries no label and should span the whole row rather than sit
   in the publish form's input column. Collapse this fieldtype's two-column
   grid and drop its (visually hidden) label cell. */
.og_image_status-fieldtype {
    display: block !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
}
.og_image_status-fieldtype > div:first-child {
    display: none !important;
}

.og-status {
    display: flex;
    gap: 12px;
    padding: 16px 18px;
    border-radius: 8px;
    border: 1px solid;
    line-height: 1.45;
}
.og-status__icon { flex: none; width: 22px; height: 22px; margin-top: 1px; }
.og-status__body { min-width: 0; }
.og-status__title { font-weight: 600; font-size: 15px; margin: 0; }
.og-status__text { font-size: 13.5px; margin: 4px 0 0; }
.og-status__hint { opacity: 0.85; }
.og-status code {
    font-size: 12.5px;
    padding: 1px 5px;
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.06);
}

.og-status__cta {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 12px;
    padding: 7px 14px;
    border-radius: 6px;
    font-size: 13.5px;
    font-weight: 600;
    text-decoration: none;
    background: #111418;
    color: #fff;
    transition: opacity 0.15s ease;
}
.og-status__cta:hover { opacity: 0.88; }
.og-status__cta svg { width: 15px; height: 15px; }

/* Warning (amber) */
.og-status--warning {
    background: #fffbeb;
    border-color: #fcd34d;
    color: #854d0e;
}
.og-status--warning .og-status__icon { color: #d97706; }

/* Success (green) */
.og-status--ok {
    background: #f0fdf4;
    border-color: #bbf7d0;
    color: #166534;
}
.og-status--ok .og-status__icon { color: #16a34a; }

/* Dark-mode friendliness for Statamic's dark CP. */
.dark .og-status code { background: rgba(255, 255, 255, 0.1); }
.dark .og-status--warning { background: rgba(217, 119, 6, 0.12); border-color: rgba(217, 119, 6, 0.5); color: #fcd34d; }
.dark .og-status--ok { background: rgba(22, 163, 74, 0.12); border-color: rgba(22, 163, 74, 0.5); color: #86efac; }
.dark .og-status__cta { background: #f8fafc; color: #111418; }
</style>
