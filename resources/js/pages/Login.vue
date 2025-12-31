<template>
    <div class="max-w-md mx-auto mt-16">
        <div class="card">
            <h2 class="text-2xl font-bold text-center mb-6">Sign In</h2>
            
            <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-6">
                <p class="font-medium">Authentication Error</p>
                <p class="text-sm mt-1">{{ errorMessage }}</p>
            </div>
            
            <p class="text-gray-600 text-center mb-6">
                Sign in with your account to continue
            </p>
            <button @click="handleLogin" class="btn btn-primary w-full">
                Sign in with OAuth
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const authStore = useAuthStore();
const errorMessage = ref('');

onMounted(() => {
    // Check for error in query parameters
    if (route.query.error) {
        errorMessage.value = route.query.error;
    }
});

function handleLogin() {
    authStore.login();
}
</script>
