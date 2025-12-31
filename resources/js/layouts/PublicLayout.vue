<template>
    <div class="min-h-screen">
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <router-link to="/" class="text-xl font-bold text-blue-600">
                            Helpdesk
                        </router-link>
                    </div>
                    <div class="flex items-center space-x-4">
                        <router-link v-if="!authStore.isAuthenticated" to="/login" class="btn btn-primary">
                            Sign In
                        </router-link>
                        <router-link v-else to="/dashboard" class="btn btn-primary">
                            Dashboard
                        </router-link>
                    </div>
                </div>
            </div>
        </nav>
        <main>
            <router-view />
        </main>
    </div>
</template>

<script setup>
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();
</script>
