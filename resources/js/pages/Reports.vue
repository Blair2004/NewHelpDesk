<template>
    <div class="p-8">
        <h1 class="text-3xl font-bold mb-8">Reports & Analytics</h1>

        <!-- Date Range Filter -->
        <div class="card mb-6">
            <div class="flex gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">From Date</label>
                    <input v-model="dateRange.from" type="date" class="input" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">To Date</label>
                    <input v-model="dateRange.to" type="date" class="input" />
                </div>
                <div class="flex items-end">
                    <button @click="loadReports" class="btn btn-primary">Generate Report</button>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="card">
                <p class="text-sm text-gray-600 mb-2">Total Threads</p>
                <p class="text-3xl font-bold text-blue-600">{{ reports.totalThreads }}</p>
            </div>
            <div class="card">
                <p class="text-sm text-gray-600 mb-2">Resolved Threads</p>
                <p class="text-3xl font-bold text-green-600">{{ reports.resolvedThreads }}</p>
            </div>
            <div class="card">
                <p class="text-sm text-gray-600 mb-2">Average Response Time</p>
                <p class="text-3xl font-bold text-yellow-600">{{ reports.avgResponseTime }}h</p>
            </div>
            <div class="card">
                <p class="text-sm text-gray-600 mb-2">Customer Satisfaction</p>
                <p class="text-3xl font-bold text-purple-600">{{ reports.satisfaction }}%</p>
            </div>
        </div>

        <!-- Thread Status Breakdown -->
        <div class="card mb-6">
            <h2 class="text-xl font-semibold mb-4">Thread Status Breakdown</h2>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span>Open</span>
                    <div class="flex items-center gap-3">
                        <div class="w-64 bg-gray-200 rounded-full h-4">
                            <div
                                class="bg-green-500 h-4 rounded-full"
                                :style="{ width: `${(reports.statusBreakdown.open / reports.totalThreads) * 100}%` }"
                            ></div>
                        </div>
                        <span class="font-semibold">{{ reports.statusBreakdown.open }}</span>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <span>Pending</span>
                    <div class="flex items-center gap-3">
                        <div class="w-64 bg-gray-200 rounded-full h-4">
                            <div
                                class="bg-yellow-500 h-4 rounded-full"
                                :style="{ width: `${(reports.statusBreakdown.pending / reports.totalThreads) * 100}%` }"
                            ></div>
                        </div>
                        <span class="font-semibold">{{ reports.statusBreakdown.pending }}</span>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <span>Resolved</span>
                    <div class="flex items-center gap-3">
                        <div class="w-64 bg-gray-200 rounded-full h-4">
                            <div
                                class="bg-blue-500 h-4 rounded-full"
                                :style="{ width: `${(reports.statusBreakdown.resolved / reports.totalThreads) * 100}%` }"
                            ></div>
                        </div>
                        <span class="font-semibold">{{ reports.statusBreakdown.resolved }}</span>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <span>Closed</span>
                    <div class="flex items-center gap-3">
                        <div class="w-64 bg-gray-200 rounded-full h-4">
                            <div
                                class="bg-gray-500 h-4 rounded-full"
                                :style="{ width: `${(reports.statusBreakdown.closed / reports.totalThreads) * 100}%` }"
                            ></div>
                        </div>
                        <span class="font-semibold">{{ reports.statusBreakdown.closed }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Categories -->
        <div class="card">
            <h2 class="text-xl font-semibold mb-4">Top Categories</h2>
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">Category</th>
                        <th class="text-right py-3 px-4">Threads</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="cat in reports.topCategories" :key="cat.id" class="border-b">
                        <td class="py-3 px-4">{{ cat.name }}</td>
                        <td class="py-3 px-4 text-right font-semibold">{{ cat.count }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const dateRange = ref({
    from: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    to: new Date().toISOString().split('T')[0],
});

const reports = ref({
    totalThreads: 0,
    resolvedThreads: 0,
    avgResponseTime: 0,
    satisfaction: 0,
    statusBreakdown: {
        open: 0,
        pending: 0,
        resolved: 0,
        closed: 0,
    },
    topCategories: [],
});

onMounted(() => {
    loadReports();
});

async function loadReports() {
    try {
        const response = await axios.get('/api/reports', {
            params: dateRange.value,
        });
        reports.value = response.data;
    } catch (error) {
        console.error('Failed to load reports:', error);
    }
}
</script>
