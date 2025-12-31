<template>
    <div class="p-8">
        <h1 class="text-3xl font-bold mb-8">Settings</h1>
        
        <div class="max-w-4xl space-y-6">
            <!-- OAuth Configuration -->
            <div class="card">
                <h2 class="text-xl font-semibold mb-4">OAuth Configuration</h2>
                <form @submit.prevent="saveOAuthSettings" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Profile Scope</label>
                        <input
                            v-model="oauthSettings.profile"
                            type="text"
                            class="input"
                            placeholder="read-profile"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Licenses Read Scope</label>
                        <input
                            v-model="oauthSettings.licenses_read"
                            type="text"
                            class="input"
                            placeholder="read-licenses"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Licenses Update Scope</label>
                        <input
                            v-model="oauthSettings.licenses_update"
                            type="text"
                            class="input"
                            placeholder="update-licenses"
                        />
                    </div>
                    <div class="flex items-center">
                        <input
                            v-model="oauthSettings.enable_licenses_update"
                            type="checkbox"
                            id="enable_licenses_update"
                            class="mr-2"
                        />
                        <label for="enable_licenses_update" class="text-sm">Enable licenses update scope</label>
                    </div>
                    <button type="submit" class="btn btn-primary" :disabled="saving">
                        {{ saving ? 'Saving...' : 'Save OAuth Settings' }}
                    </button>
                </form>
            </div>

            <!-- License Filter Configuration -->
            <div class="card">
                <h2 class="text-xl font-semibold mb-4">License Validation</h2>
                <form @submit.prevent="saveLicenseSettings" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Filter Field</label>
                        <input
                            v-model="licenseSettings.filter_field"
                            type="text"
                            class="input"
                            placeholder="item_id"
                        />
                        <p class="text-sm text-gray-600 mt-1">The field name to filter licenses by</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Allowed Values</label>
                        <input
                            v-model="licenseSettings.filter_values"
                            type="text"
                            class="input"
                            placeholder="123,456,789"
                        />
                        <p class="text-sm text-gray-600 mt-1">Comma-separated list of allowed values</p>
                    </div>
                    <button type="submit" class="btn btn-primary" :disabled="saving">
                        {{ saving ? 'Saving...' : 'Save License Settings' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const oauthSettings = ref({
    profile: '',
    licenses_read: '',
    licenses_update: '',
    enable_licenses_update: false,
});

const licenseSettings = ref({
    filter_field: '',
    filter_values: '',
});

const saving = ref(false);

onMounted(async () => {
    await loadSettings();
});

async function loadSettings() {
    try {
        const response = await axios.get('/api/settings');
        const settings = response.data;
        
        oauthSettings.value = {
            profile: settings.oauth_scope_profile || '',
            licenses_read: settings.oauth_scope_licenses_read || '',
            licenses_update: settings.oauth_scope_licenses_update || '',
            enable_licenses_update: settings.oauth_enable_licenses_update || false,
        };
        
        licenseSettings.value = {
            filter_field: settings.license_filter_field || '',
            filter_values: settings.license_filter_values || '',
        };
    } catch (error) {
        console.error('Failed to load settings:', error);
    }
}

async function saveOAuthSettings() {
    saving.value = true;
    try {
        await axios.put('/api/settings/oauth-scopes', oauthSettings.value);
        alert('OAuth settings saved successfully');
    } catch (error) {
        alert('Failed to save settings');
        console.error(error);
    } finally {
        saving.value = false;
    }
}

async function saveLicenseSettings() {
    saving.value = true;
    try {
        await axios.put('/api/settings', licenseSettings.value);
        alert('License settings saved successfully');
    } catch (error) {
        alert('Failed to save settings');
        console.error(error);
    } finally {
        saving.value = false;
    }
}
</script>
