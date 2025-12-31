import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const loading = ref(false);

    const isAuthenticated = computed(() => !!user.value);
    const isStaff = computed(() => user.value?.is_staff || false);
    const isAdmin = computed(() => user.value?.is_admin || false);

    async function checkAuth() {
        try {
            const response = await axios.get('/api/user');
            user.value = response.data;
        } catch (error) {
            user.value = null;
        }
    }

    async function login() {
        window.location.href = '/oauth/redirect';
    }

    async function logout() {
        try {
            await axios.post('/logout');
            user.value = null;
            window.location.href = '/';
        } catch (error) {
            console.error('Logout failed:', error);
        }
    }

    return {
        user,
        loading,
        isAuthenticated,
        isStaff,
        isAdmin,
        checkAuth,
        login,
        logout,
    };
});
