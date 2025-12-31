<template>
    <div class="p-8">
        <h1 class="text-3xl font-bold mb-8">Profile</h1>
        <div class="max-w-2xl">
            <div class="card mb-6">
                <h2 class="text-xl font-semibold mb-4">Personal Information</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Name</label>
                        <input type="text" :value="authStore.user?.name" readonly class="input bg-gray-50" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Email</label>
                        <input type="email" :value="authStore.user?.email" readonly class="input bg-gray-50" />
                    </div>
                    <p class="text-sm text-gray-600">
                        Name and email are managed by your OAuth provider and cannot be changed here.
                    </p>
                </div>
            </div>

            <div class="card">
                <h2 class="text-xl font-semibold mb-4">Preferences</h2>
                <form @submit.prevent="updatePreferences">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Preferred Language</label>
                            <select v-model="preferences.locale" class="input">
                                <option value="en">English</option>
                                <option value="es">Spanish</option>
                                <option value="fr">French</option>
                                <option value="de">German</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Timezone</label>
                            <input v-model="preferences.timezone" type="text" class="input" />
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';

const authStore = useAuthStore();
const preferences = ref({
    locale: 'en',
    timezone: '',
});

onMounted(() => {
    if (authStore.user) {
        preferences.value.locale = authStore.user.preferred_locale;
        preferences.value.timezone = authStore.user.timezone || '';
    }
});

async function updatePreferences() {
    try {
        await axios.put('/api/user/preferences', preferences.value);
        alert('Preferences updated successfully');
        await authStore.checkAuth();
    } catch (error) {
        console.error('Failed to update preferences:', error);
        alert('Failed to update preferences');
    }
}
</script>
