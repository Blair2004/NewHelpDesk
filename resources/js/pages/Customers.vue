<template>
    <div class="p-8">
        <h1 class="text-3xl font-bold mb-8">Customers</h1>

        <div class="card mb-6">
            <div class="flex gap-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search customers..."
                    class="input flex-1"
                    @input="searchCustomers"
                />
                <select v-model="statusFilter" @change="searchCustomers" class="input">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div class="card">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">Name</th>
                        <th class="text-left py-3 px-4">Email</th>
                        <th class="text-left py-3 px-4">Licenses</th>
                        <th class="text-left py-3 px-4">Threads</th>
                        <th class="text-left py-3 px-4">Last Login</th>
                        <th class="text-left py-3 px-4">Status</th>
                        <th class="text-right py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="customer in customers" :key="customer.id" class="border-b">
                        <td class="py-3 px-4 font-medium">{{ customer.name }}</td>
                        <td class="py-3 px-4 text-gray-600">{{ customer.email }}</td>
                        <td class="py-3 px-4">{{ customer.licenses_count || 0 }}</td>
                        <td class="py-3 px-4">{{ customer.threads_count || 0 }}</td>
                        <td class="py-3 px-4 text-sm text-gray-600">
                            {{ formatDate(customer.last_login_at) }}
                        </td>
                        <td class="py-3 px-4">
                            <span :class="statusBadge(customer.is_active)">
                                {{ customer.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <button @click="viewCustomer(customer.id)" class="text-blue-600 hover:underline mr-3">
                                View
                            </button>
                            <button
                                @click="toggleStatus(customer)"
                                :class="customer.is_active ? 'text-red-600' : 'text-green-600'"
                                class="hover:underline"
                            >
                                {{ customer.is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import moment from 'moment';

const customers = ref([]);
const searchQuery = ref('');
const statusFilter = ref('');

onMounted(() => {
    loadCustomers();
});

async function loadCustomers() {
    try {
        const params = {};
        if (searchQuery.value) params.search = searchQuery.value;
        if (statusFilter.value) params.status = statusFilter.value;
        
        const response = await axios.get('/api/customers', { params });
        customers.value = response.data.data || response.data;
    } catch (error) {
        console.error('Failed to load customers:', error);
    }
}

function searchCustomers() {
    loadCustomers();
}

function viewCustomer(id) {
    // Navigate to customer detail page (to be implemented)
    console.log('View customer:', id);
}

async function toggleStatus(customer) {
    try {
        await axios.put(`/api/customers/${customer.id}/status`, {
            is_active: !customer.is_active,
        });
        await loadCustomers();
    } catch (error) {
        alert('Failed to update customer status');
        console.error(error);
    }
}

function formatDate(date) {
    return date ? moment(date).fromNow() : 'Never';
}

function statusBadge(isActive) {
    return isActive
        ? 'inline-block px-2 py-1 text-xs rounded bg-green-100 text-green-800'
        : 'inline-block px-2 py-1 text-xs rounded bg-gray-100 text-gray-800';
}
</script>
