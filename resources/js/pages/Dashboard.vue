<template>
    <div class="p-8">
        <h1 class="text-3xl font-bold mb-8">Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="card">
                <p class="text-sm text-gray-600 mb-2">New Threads</p>
                <p class="text-3xl font-bold text-blue-600">{{ stats.newThreads }}</p>
            </div>
            <div class="card">
                <p class="text-sm text-gray-600 mb-2">Open Threads</p>
                <p class="text-3xl font-bold text-green-600">{{ stats.openThreads }}</p>
            </div>
            <div class="card">
                <p class="text-sm text-gray-600 mb-2">Pending Threads</p>
                <p class="text-3xl font-bold text-yellow-600">{{ stats.pendingThreads }}</p>
            </div>
            <div class="card">
                <p class="text-sm text-gray-600 mb-2">Closed Threads</p>
                <p class="text-3xl font-bold text-gray-600">{{ stats.closedThreads }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card">
                <h2 class="text-xl font-semibold mb-4">Recent Threads</h2>
                <div class="space-y-4">
                    <div
                        v-for="thread in recentThreads"
                        :key="thread.id"
                        class="p-4 border rounded-lg hover:bg-gray-50 cursor-pointer"
                        @click="$router.push(`/dashboard/threads/${thread.id}`)"
                    >
                        <h3 class="font-medium">{{ thread.title }}</h3>
                        <p class="text-sm text-gray-600">{{ thread.owner.name }}</p>
                        <span
                            :class="statusClass(thread.status)"
                            class="inline-block px-2 py-1 text-xs rounded mt-2"
                        >
                            {{ thread.status }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2 class="text-xl font-semibold mb-4">Assigned to Me</h2>
                <div class="space-y-4">
                    <div
                        v-for="thread in assignedThreads"
                        :key="thread.id"
                        class="p-4 border rounded-lg hover:bg-gray-50 cursor-pointer"
                        @click="$router.push(`/dashboard/threads/${thread.id}`)"
                    >
                        <h3 class="font-medium">{{ thread.title }}</h3>
                        <p class="text-sm text-gray-600">{{ thread.owner.name }}</p>
                        <span
                            :class="statusClass(thread.status)"
                            class="inline-block px-2 py-1 text-xs rounded mt-2"
                        >
                            {{ thread.status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const stats = ref({
    newThreads: 0,
    openThreads: 0,
    pendingThreads: 0,
    closedThreads: 0,
});

const recentThreads = ref([]);
const assignedThreads = ref([]);

onMounted(async () => {
    await loadDashboardData();
});

async function loadDashboardData() {
    try {
        // Load recent threads
        const recentResponse = await axios.get('/api/threads', {
            params: { limit: 5 },
        });
        recentThreads.value = recentResponse.data.data;

        // Load assigned threads
        const assignedResponse = await axios.get('/api/threads', {
            params: { assigned_to_me: true, limit: 5 },
        });
        assignedThreads.value = assignedResponse.data.data;

        // Calculate stats
        const statsResponse = await axios.get('/api/threads/stats');
        stats.value = statsResponse.data;
    } catch (error) {
        console.error('Failed to load dashboard data:', error);
    }
}

function statusClass(status) {
    const classes = {
        open: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        resolved: 'bg-blue-100 text-blue-800',
        closed: 'bg-gray-100 text-gray-800',
    };
    return classes[status] || classes.open;
}
</script>
