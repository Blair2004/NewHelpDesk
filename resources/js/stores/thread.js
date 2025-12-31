import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useThreadStore = defineStore('thread', () => {
    const threads = ref([]);
    const currentThread = ref(null);
    const loading = ref(false);
    const filters = ref({
        status: null,
        category_id: null,
        assigned_to_me: false,
        unassigned: false,
    });

    async function fetchThreads() {
        loading.value = true;
        try {
            const response = await axios.get('/api/threads', { params: filters.value });
            threads.value = response.data.data;
        } catch (error) {
            console.error('Failed to fetch threads:', error);
        } finally {
            loading.value = false;
        }
    }

    async function fetchThread(id) {
        loading.value = true;
        try {
            const response = await axios.get(`/api/threads/${id}`);
            currentThread.value = response.data.data;
        } catch (error) {
            console.error('Failed to fetch thread:', error);
        } finally {
            loading.value = false;
        }
    }

    async function createThread(data) {
        loading.value = true;
        try {
            const response = await axios.post('/api/threads', data);
            threads.value.unshift(response.data.data);
            return response.data.data;
        } catch (error) {
            console.error('Failed to create thread:', error);
            throw error;
        } finally {
            loading.value = false;
        }
    }

    async function updateThread(id, data) {
        loading.value = true;
        try {
            const response = await axios.put(`/api/threads/${id}`, data);
            const index = threads.value.findIndex(t => t.id === id);
            if (index !== -1) {
                threads.value[index] = response.data.data;
            }
            if (currentThread.value?.id === id) {
                currentThread.value = response.data.data;
            }
            return response.data.data;
        } catch (error) {
            console.error('Failed to update thread:', error);
            throw error;
        } finally {
            loading.value = false;
        }
    }

    async function deleteThread(id) {
        loading.value = true;
        try {
            await axios.delete(`/api/threads/${id}`);
            threads.value = threads.value.filter(t => t.id !== id);
        } catch (error) {
            console.error('Failed to delete thread:', error);
            throw error;
        } finally {
            loading.value = false;
        }
    }

    async function sendMessage(threadId, data) {
        try {
            const response = await axios.post(`/api/threads/${threadId}/messages`, data);
            if (currentThread.value?.id === threadId) {
                currentThread.value.messages.push(response.data.data);
            }
            return response.data.data;
        } catch (error) {
            console.error('Failed to send message:', error);
            throw error;
        }
    }

    return {
        threads,
        currentThread,
        loading,
        filters,
        fetchThreads,
        fetchThread,
        createThread,
        updateThread,
        deleteThread,
        sendMessage,
    };
});
