<template>
    <div class="p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Threads</h1>
            <button @click="showCreateModal = true" class="btn btn-primary">
                New Thread
            </button>
        </div>

        <!-- Filters -->
        <div class="card mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Status</label>
                    <select v-model="threadStore.filters.status" @change="threadStore.fetchThreads" class="input">
                        <option :value="null">All</option>
                        <option value="open">Open</option>
                        <option value="pending">Pending</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            v-model="threadStore.filters.assigned_to_me"
                            @change="threadStore.fetchThreads"
                            class="mr-2"
                        />
                        <span class="text-sm">Assigned to me</span>
                    </label>
                </div>
                <div class="flex items-end">
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            v-model="threadStore.filters.unassigned"
                            @change="threadStore.fetchThreads"
                            class="mr-2"
                        />
                        <span class="text-sm">Unassigned</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Threads List -->
        <div class="space-y-4">
            <div
                v-for="thread in threadStore.threads"
                :key="thread.id"
                class="card hover:shadow-md transition-shadow cursor-pointer"
                @click="$router.push(`/dashboard/threads/${thread.id}`)"
            >
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold mb-2">{{ thread.title }}</h3>
                        <div class="flex items-center space-x-4 text-sm text-gray-600">
                            <span>{{ thread.owner.name }}</span>
                            <span>{{ formatDate(thread.created_at) }}</span>
                            <span v-if="thread.category" class="px-2 py-1 bg-gray-100 rounded">
                                {{ thread.category.name }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span :class="statusClass(thread.status)" class="px-3 py-1 text-sm rounded">
                            {{ thread.status }}
                        </span>
                        <LockClosedIcon v-if="thread.is_locked" class="w-5 h-5 text-gray-500" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Thread Modal -->
        <div
            v-if="showCreateModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
            @click.self="showCreateModal = false"
        >
            <div class="bg-white rounded-lg p-6 max-w-2xl w-full">
                <h2 class="text-2xl font-bold mb-4">Create New Thread</h2>
                <form @submit.prevent="createThread">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Title</label>
                        <input v-model="newThread.title" type="text" class="input" required />
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Message</label>
                        <textarea v-model="newThread.content" rows="6" class="input" required></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Visibility</label>
                        <select v-model="newThread.visibility" class="input">
                            <option value="public">Public</option>
                            <option value="private">Private</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" @click="showCreateModal = false" class="btn btn-secondary">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useThreadStore } from '../stores/thread';
import { LockClosedIcon } from '@heroicons/vue/24/solid';
import moment from 'moment';

const router = useRouter();
const threadStore = useThreadStore();
const showCreateModal = ref(false);
const newThread = ref({
    title: '',
    content: '',
    visibility: 'public',
});

onMounted(() => {
    threadStore.fetchThreads();
});

async function createThread() {
    try {
        const thread = await threadStore.createThread(newThread.value);
        showCreateModal.value = false;
        newThread.value = { title: '', content: '', visibility: 'public' };
        router.push(`/dashboard/threads/${thread.id}`);
    } catch (error) {
        console.error('Failed to create thread:', error);
    }
}

function formatDate(date) {
    return moment(date).fromNow();
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
