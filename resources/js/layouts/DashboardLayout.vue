<template>
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg">
            <div class="p-4">
                <h1 class="text-xl font-bold text-blue-600">Helpdesk</h1>
            </div>
            <nav class="mt-6">
                <router-link
                    v-for="item in navItems"
                    :key="item.name"
                    :to="item.to"
                    class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors"
                    active-class="bg-blue-50 text-blue-600 border-r-4 border-blue-600"
                >
                    <component :is="item.icon" class="w-5 h-5 mr-3" />
                    {{ item.name }}
                </router-link>
            </nav>
            <div class="absolute bottom-0 w-64 p-4 border-t">
                <div class="flex items-center">
                    <img
                        :src="authStore.user?.avatar || '/default-avatar.png'"
                        alt="Avatar"
                        class="w-10 h-10 rounded-full"
                    />
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-700">{{ authStore.user?.name }}</p>
                        <button @click="authStore.logout" class="text-xs text-gray-500 hover:text-gray-700">
                            Sign out
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 overflow-auto">
            <router-view />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import {
    HomeIcon,
    ChatBubbleLeftRightIcon,
    BuildingOfficeIcon,
    UsersIcon,
    ChartBarIcon,
    CogIcon,
    UserCircleIcon,
} from '@heroicons/vue/24/outline';

const authStore = useAuthStore();

const navItems = computed(() => {
    const items = [
        { name: 'Home', to: '/dashboard', icon: HomeIcon },
        { name: 'Threads', to: '/dashboard/threads', icon: ChatBubbleLeftRightIcon },
        { name: 'Profile', to: '/dashboard/profile', icon: UserCircleIcon },
    ];

    if (authStore.isStaff) {
        items.push(
            { name: 'Departments', to: '/dashboard/departments', icon: BuildingOfficeIcon },
            { name: 'Customers', to: '/dashboard/customers', icon: UsersIcon },
            { name: 'Reports', to: '/dashboard/reports', icon: ChartBarIcon },
            { name: 'Settings', to: '/dashboard/settings', icon: CogIcon }
        );
    }

    return items;
});
</script>
