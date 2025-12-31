<template>
    <div v-if="threadStore.currentThread" class="flex h-full">
        <!-- Chat Area -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <div class="bg-white border-b p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-bold">{{ threadStore.currentThread.title }}</h1>
                        <p class="text-sm text-gray-600 mt-1">
                            Created by {{ threadStore.currentThread.owner.name }}
                        </p>
                    </div>
                    <span :class="statusClass(threadStore.currentThread.status)" class="px-3 py-1 text-sm rounded">
                        {{ threadStore.currentThread.status }}
                    </span>
                </div>
            </div>

            <!-- Messages -->
            <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
                <TransitionGroup name="message">
                    <div
                        v-for="message in threadStore.currentThread.messages"
                        :key="message.id"
                        :class="[
                            'flex',
                            message.author.id === authStore.user?.id ? 'justify-end' : 'justify-start',
                        ]"
                    >
                        <div
                            :class="[
                                'max-w-xl p-4 rounded-lg',
                                message.author.id === authStore.user?.id
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-white border',
                            ]"
                        >
                            <div class="flex items-center mb-2">
                                <img
                                    :src="message.author.avatar || '/default-avatar.png'"
                                    alt="Avatar"
                                    class="w-6 h-6 rounded-full mr-2"
                                />
                                <span class="text-sm font-medium">{{ message.author.name }}</span>
                                <span class="text-xs ml-2 opacity-75">{{ formatDate(message.created_at) }}</span>
                            </div>
                            <p class="whitespace-pre-wrap">{{ message.content }}</p>
                            <div v-if="message.attachments?.length" class="mt-2 space-y-2">
                                <img
                                    v-for="attachment in message.attachments"
                                    :key="attachment.id"
                                    :src="attachment.url"
                                    :alt="attachment.filename"
                                    class="rounded max-w-sm"
                                />
                            </div>
                        </div>
                    </div>
                </TransitionGroup>
            </div>

            <!-- Message Input -->
            <div v-if="!threadStore.currentThread.is_locked" class="bg-white border-t p-4">
                <form @submit.prevent="sendMessage" class="flex space-x-4">
                    <textarea
                        v-model="newMessage"
                        placeholder="Type your message..."
                        rows="3"
                        class="input flex-1"
                        @keydown.ctrl.enter="sendMessage"
                    ></textarea>
                    <button type="submit" class="btn btn-primary self-end">Send</button>
                </form>
            </div>
            <div v-else class="bg-yellow-50 border-t p-4 text-center text-sm text-yellow-800">
                This thread is locked. No new messages can be added.
            </div>
        </div>

        <!-- Right Panel -->
        <div class="w-80 bg-white border-l p-6 overflow-y-auto">
            <h3 class="font-semibold mb-4">Thread Details</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Status</label>
                    <select v-model="localStatus" @change="updateThread" class="input">
                        <option value="open">Open</option>
                        <option value="pending">Pending</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Category</label>
                    <p class="text-gray-700">{{ threadStore.currentThread.category?.name || 'None' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Locale</label>
                    <p class="text-gray-700">{{ threadStore.currentThread.locale }}</p>
                </div>

                <div v-if="authStore.isStaff">
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            v-model="localLocked"
                            @change="updateThread"
                            class="mr-2"
                        />
                        <span class="text-sm">Lock thread</span>
                    </label>
                </div>

                <div>
                    <h4 class="font-medium mb-2">Participants</h4>
                    <div class="space-y-2">
                        <div
                            v-for="participant in threadStore.currentThread.participants"
                            :key="participant.id"
                            class="flex items-center"
                        >
                            <img
                                :src="participant.avatar || '/default-avatar.png'"
                                alt="Avatar"
                                class="w-8 h-8 rounded-full mr-2"
                            />
                            <span class="text-sm">{{ participant.name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import { useThreadStore } from '../stores/thread';
import { useAuthStore } from '../stores/auth';
import moment from 'moment';

const route = useRoute();
const threadStore = useThreadStore();
const authStore = useAuthStore();
const messagesContainer = ref(null);
const newMessage = ref('');
const localStatus = ref('');
const localLocked = ref(false);

onMounted(async () => {
    await threadStore.fetchThread(route.params.id);
    localStatus.value = threadStore.currentThread.status;
    localLocked.value = threadStore.currentThread.is_locked;
    scrollToBottom();
});

watch(
    () => threadStore.currentThread?.messages,
    () => {
        nextTick(() => scrollToBottom());
    },
    { deep: true }
);

async function sendMessage() {
    if (!newMessage.value.trim()) return;

    try {
        await threadStore.sendMessage(route.params.id, {
            content: newMessage.value,
        });
        newMessage.value = '';
    } catch (error) {
        console.error('Failed to send message:', error);
    }
}

async function updateThread() {
    try {
        await threadStore.updateThread(route.params.id, {
            status: localStatus.value,
            is_locked: localLocked.value,
        });
    } catch (error) {
        console.error('Failed to update thread:', error);
    }
}

function scrollToBottom() {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
}

function formatDate(date) {
    return moment(date).format('MMM D, YYYY h:mm A');
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

<style scoped>
.message-enter-active {
    animation: message-in 0.3s ease-out;
}

.message-leave-active {
    animation: message-out 0.3s ease-in;
}

@keyframes message-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes message-out {
    from {
        opacity: 1;
        transform: translateY(0);
    }
    to {
        opacity: 0;
        transform: translateY(-10px);
    }
}

@media (prefers-reduced-motion: reduce) {
    .message-enter-active,
    .message-leave-active {
        animation: none;
    }
}
</style>
