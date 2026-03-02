<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from '../../composables/useToast';

const toast = useToast();
const categories = ref([]);
const loading = ref(true);

const categoryCreateName = ref('');
const categoryCreateOpen = ref(false);
const categoryCreateLoading = ref(false);
const categoryEditId = ref(null);
const categoryEditName = ref('');
const categoryEditOpen = ref(false);
const categoryEditLoading = ref(false);

async function fetchCategories() {
    try {
        const { data } = await axios.get('/api/administration/templatecategoryindex');
        const payload = data?.data ?? data;
        categories.value = payload?.categories ?? [];
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Failed to load categories.';
        toast.error(msg);
        categories.value = [];
    } finally {
        loading.value = false;
    }
}

function openCategoryCreate() {
    categoryCreateOpen.value = true;
    categoryCreateName.value = '';
}

function closeCategoryCreate() {
    categoryCreateOpen.value = false;
}

async function submitCategoryCreate() {
    const name = categoryCreateName.value?.trim();
    if (!name) {
        toast.error('Name is required.');
        return;
    }
    categoryCreateLoading.value = true;
    try {
        await axios.post('/api/administration/templatecategorycreate', { name });
        closeCategoryCreate();
        await fetchCategories();
        toast.success('Category created.');
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Could not create category.';
        toast.error(msg);
    } finally {
        categoryCreateLoading.value = false;
    }
}

function openCategoryEdit(cat) {
    categoryEditId.value = cat.id;
    categoryEditName.value = cat.name;
    categoryEditOpen.value = true;
}

function closeCategoryEdit() {
    categoryEditOpen.value = false;
    categoryEditId.value = null;
}

async function submitCategoryEdit() {
    const name = categoryEditName.value?.trim();
    if (!name) {
        toast.error('Name is required.');
        return;
    }
    categoryEditLoading.value = true;
    try {
        await axios.post(`/api/administration/templatecategoryupdate/${categoryEditId.value}`, { name });
        closeCategoryEdit();
        await fetchCategories();
        toast.success('Category updated.');
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Could not update category.';
        toast.error(msg);
    } finally {
        categoryEditLoading.value = false;
    }
}

async function deleteCategory(cat) {
    if (!confirm(`Remove category "${cat.name}"? This will unlink it from all templates.`)) return;
    try {
        await axios.post(`/api/administration/templatecategorydelete/${cat.id}`);
        await fetchCategories();
        toast.success('Category removed.');
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Could not remove category.';
        toast.error(msg);
    }
}

onMounted(() => fetchCategories());
</script>

<template>
    <div class="max-w-4xl">
        <div>
            <h1 class="text-xl font-semibold text-site-heading">Template Categories</h1>
            <p class="mt-1 text-sm text-site-body">Create, edit, and remove template categories.</p>
        </div>

        <div v-if="loading" class="mt-6 rounded-xl border border-slate-700 bg-slate-900/50 p-8 text-center text-site-body">
            Loading…
        </div>

        <section v-else class="mt-6 rounded-xl border border-slate-700 bg-slate-900/50 p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-site-heading">Categories</h2>
                <button
                    type="button"
                    class="rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500"
                    @click="openCategoryCreate"
                >
                    Add category
                </button>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-700">
                    <thead>
                        <tr>
                            <th class="py-2 text-left text-sm font-medium text-site-body">Name</th>
                            <th class="py-2 text-right text-sm font-medium text-site-body">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="cat in categories" :key="cat.id">
                            <td class="py-2 text-site-body">{{ cat.name }}</td>
                            <td class="py-2 text-right">
                                <button
                                    type="button"
                                    class="text-indigo-400 hover:text-indigo-300"
                                    @click="openCategoryEdit(cat)"
                                >
                                    Edit
                                </button>
                                <span class="mx-2 text-slate-600">|</span>
                                <button
                                    type="button"
                                    class="text-red-400 hover:text-red-300"
                                    @click="deleteCategory(cat)"
                                >
                                    Remove
                                </button>
                            </td>
                        </tr>
                        <tr v-if="categories.length === 0">
                            <td colspan="2" class="py-6 text-center text-sm text-slate-500">No categories yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Category create modal -->
        <div
            v-show="categoryCreateOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
            @click.self="closeCategoryCreate"
        >
            <div class="w-full max-w-md rounded-xl border border-slate-700 bg-slate-900 p-6 shadow-xl">
                <h3 class="text-lg font-medium text-site-heading">Add category</h3>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-site-body">Name</label>
                    <input
                        v-model="categoryCreateName"
                        type="text"
                        class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-body"
                        placeholder="Category name"
                        @keydown.enter="submitCategoryCreate"
                    />
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-lg px-4 py-2 text-site-body hover:bg-slate-700"
                        @click="closeCategoryCreate"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500 disabled:opacity-50"
                        :disabled="categoryCreateLoading"
                        @click="submitCategoryCreate"
                    >
                        {{ categoryCreateLoading ? 'Creating…' : 'Create' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Category edit modal -->
        <div
            v-show="categoryEditOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
            @click.self="closeCategoryEdit"
        >
            <div class="w-full max-w-md rounded-xl border border-slate-700 bg-slate-900 p-6 shadow-xl">
                <h3 class="text-lg font-medium text-site-heading">Edit category</h3>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-site-body">Name</label>
                    <input
                        v-model="categoryEditName"
                        type="text"
                        class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-body"
                        @keydown.enter="submitCategoryEdit"
                    />
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-lg px-4 py-2 text-site-body hover:bg-slate-700"
                        @click="closeCategoryEdit"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500 disabled:opacity-50"
                        :disabled="categoryEditLoading"
                        @click="submitCategoryEdit"
                    >
                        {{ categoryEditLoading ? 'Saving…' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
