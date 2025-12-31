<template>
    <div class="p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Departments</h1>
            <button @click="showCreateModal = true" class="btn btn-primary">
                Create Department
            </button>
        </div>

        <div class="card">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">Name</th>
                        <th class="text-left py-3 px-4">Description</th>
                        <th class="text-left py-3 px-4">Members</th>
                        <th class="text-right py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="dept in departments" :key="dept.id" class="border-b">
                        <td class="py-3 px-4 font-medium">{{ dept.name }}</td>
                        <td class="py-3 px-4 text-gray-600">{{ dept.description || 'N/A' }}</td>
                        <td class="py-3 px-4">{{ dept.members_count || 0 }}</td>
                        <td class="py-3 px-4 text-right">
                            <button @click="editDepartment(dept)" class="text-blue-600 hover:underline mr-3">Edit</button>
                            <button @click="deleteDepartment(dept.id)" class="text-red-600 hover:underline">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showCreateModal" class="modal">
            <div class="modal-content">
                <h2 class="text-xl font-bold mb-4">{{ editing ? 'Edit' : 'Create' }} Department</h2>
                <form @submit.prevent="saveDepartment" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Name</label>
                        <input v-model="form.name" type="text" class="input" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Description</label>
                        <textarea v-model="form.description" class="input" rows="3"></textarea>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button type="button" @click="closeModal" class="btn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const departments = ref([]);
const showCreateModal = ref(false);
const editing = ref(false);
const form = ref({ name: '', description: '' });

onMounted(() => {
    loadDepartments();
});

async function loadDepartments() {
    try {
        const response = await axios.get('/api/departments');
        departments.value = response.data;
    } catch (error) {
        console.error('Failed to load departments:', error);
    }
}

function editDepartment(dept) {
    editing.value = true;
    form.value = { ...dept };
    showCreateModal.value = true;
}

async function saveDepartment() {
    try {
        if (editing.value) {
            await axios.put(`/api/departments/${form.value.id}`, form.value);
        } else {
            await axios.post('/api/departments', form.value);
        }
        closeModal();
        await loadDepartments();
    } catch (error) {
        alert('Failed to save department');
        console.error(error);
    }
}

async function deleteDepartment(id) {
    if (!confirm('Are you sure you want to delete this department?')) return;
    
    try {
        await axios.delete(`/api/departments/${id}`);
        await loadDepartments();
    } catch (error) {
        alert('Failed to delete department');
        console.error(error);
    }
}

function closeModal() {
    showCreateModal.value = false;
    editing.value = false;
    form.value = { name: '', description: '' };
}
</script>
