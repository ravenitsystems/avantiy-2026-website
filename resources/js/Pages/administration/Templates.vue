<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from '../../composables/useToast';

const toast = useToast();
const categories = ref([]);
const templates = ref([]);
const loading = ref(true);

const templateEditId = ref(null);
const templateEditForm = ref({ enabled: true, order_offset: 1000, front_page: false, name: '', category_ids: [] });
const templateEditOpen = ref(false);
const templateEditLoading = ref(false);

async function fetchCategories() {
    try {
        const { data } = await axios.get('/api/administration/templatecategoryindex');
        const payload = data?.data ?? data;
        categories.value = payload?.categories ?? [];
    } catch {
        categories.value = [];
    }
}

async function fetchTemplates() {
    try {
        const { data } = await axios.get('/api/administration/templateadminindex');
        const payload = data?.data ?? data;
        templates.value = payload?.templates ?? [];
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Failed to load templates.';
        toast.error(msg);
        templates.value = [];
    } finally {
        loading.value = false;
    }
}

async function loadAll() {
    loading.value = true;
    await Promise.all([fetchCategories(), fetchTemplates()]);
    loading.value = false;
}

function openTemplateEdit(t) {
    templateEditId.value = t.id;
    templateEditForm.value = {
        enabled: t.enabled,
        order_offset: t.order_offset,
        front_page: t.front_page,
        name: t.name ?? '',
        category_ids: [...(t.category_ids ?? [])],
    };
    templateEditOpen.value = true;
}

function closeTemplateEdit() {
    templateEditOpen.value = false;
    templateEditId.value = null;
}

function toggleCategory(catId) {
    const ids = templateEditForm.value.category_ids;
    const i = ids.indexOf(catId);
    if (i === -1) {
        templateEditForm.value.category_ids = [...ids, catId];
    } else {
        templateEditForm.value.category_ids = ids.filter((id) => id !== catId);
    }
}

function isCategorySelected(catId) {
    return templateEditForm.value.category_ids.includes(catId);
}

async function submitTemplateEdit() {
    templateEditLoading.value = true;
    try {
        await axios.post(`/api/administration/templateadminupdate/${templateEditId.value}`, {
            enabled: templateEditForm.value.enabled,
            order_offset: Number(templateEditForm.value.order_offset) || 0,
            front_page: templateEditForm.value.front_page,
            name: String(templateEditForm.value.name ?? '').trim(),
        });
        await axios.post(`/api/administration/templateadminupdatecategories/${templateEditId.value}`, {
            category_ids: templateEditForm.value.category_ids,
        });
        closeTemplateEdit();
        await fetchTemplates();
        toast.success('Template updated.');
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Could not update template.';
        toast.error(msg);
    } finally {
        templateEditLoading.value = false;
    }
}

onMounted(() => loadAll());
</script>

<template>
    <div class="max-w-5xl">
        <div>
            <h1 class="text-xl font-semibold text-site-heading">Templates</h1>
            <p class="mt-1 text-sm text-site-body">Edit enabled, order offset, front page, name, and category assignments. These fields are preserved when templates are synced from Duda.</p>
        </div>

        <div v-if="loading" class="mt-6 rounded-xl border border-slate-700 bg-slate-900/50 p-8 text-center text-site-body">
            Loading…
        </div>

        <section v-else class="mt-6 rounded-xl border border-slate-700 bg-slate-900/50 p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-700">
                    <thead>
                        <tr>
                            <th class="py-2 text-left text-sm font-medium text-site-body">Preview</th>
                            <th class="py-2 text-left text-sm font-medium text-site-body">Name</th>
                            <th class="py-2 text-left text-sm font-medium text-site-body">Categories</th>
                            <th class="py-2 text-left text-sm font-medium text-site-body">Enabled</th>
                            <th class="py-2 text-left text-sm font-medium text-site-body">Order</th>
                            <th class="py-2 text-left text-sm font-medium text-site-body">Front page</th>
                            <th class="py-2 text-right text-sm font-medium text-site-body">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="t in templates" :key="t.id">
                            <td class="py-2">
                                <img
                                    v-if="t.thumbnail_url"
                                    :src="t.thumbnail_url"
                                    alt=""
                                    class="h-12 w-20 rounded object-cover"
                                />
                                <span v-else class="text-slate-500">—</span>
                            </td>
                            <td class="py-2 text-site-body">{{ t.name }}</td>
                            <td class="py-2 text-site-body">
                                <span v-if="t.category_ids?.length" class="text-sm">
                                    {{ t.category_ids.length }} categor{{ t.category_ids.length === 1 ? 'y' : 'ies' }}
                                </span>
                                <span v-else class="text-slate-500">—</span>
                            </td>
                            <td class="py-2 text-site-body">{{ t.enabled ? 'Yes' : 'No' }}</td>
                            <td class="py-2 text-site-body">{{ t.order_offset }}</td>
                            <td class="py-2 text-site-body">{{ t.front_page ? 'Yes' : 'No' }}</td>
                            <td class="py-2 text-right">
                                <button
                                    type="button"
                                    class="text-indigo-400 hover:text-indigo-300"
                                    @click="openTemplateEdit(t)"
                                >
                                    Edit
                                </button>
                            </td>
                        </tr>
                        <tr v-if="templates.length === 0">
                            <td colspan="7" class="py-6 text-center text-sm text-slate-500">No templates yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Template edit modal -->
        <div
            v-show="templateEditOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 overflow-y-auto py-8"
            @click.self="closeTemplateEdit"
        >
            <div class="my-auto w-full max-w-md rounded-xl border border-slate-700 bg-slate-900 p-6 shadow-xl">
                <h3 class="text-lg font-medium text-site-heading">Edit template</h3>
                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-site-body">Name</label>
                        <input
                            v-model="templateEditForm.name"
                            type="text"
                            class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-body"
                            maxlength="128"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-site-body">Order offset</label>
                        <input
                            v-model.number="templateEditForm.order_offset"
                            type="number"
                            class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-body"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            v-model="templateEditForm.enabled"
                            type="checkbox"
                            id="tpl-enabled"
                            class="rounded border-slate-600"
                        />
                        <label for="tpl-enabled" class="text-sm text-site-body">Enabled</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            v-model="templateEditForm.front_page"
                            type="checkbox"
                            id="tpl-front"
                            class="rounded border-slate-600"
                        />
                        <label for="tpl-front" class="text-sm text-site-body">Front page</label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-site-body mb-2">Categories</label>
                        <div class="max-h-40 overflow-y-auto rounded-lg border border-slate-600 bg-slate-800 p-2 space-y-2">
                            <div
                                v-for="cat in categories"
                                :key="cat.id"
                                class="flex items-center gap-2"
                            >
                                <input
                                    :id="`tpl-cat-${cat.id}`"
                                    type="checkbox"
                                    :checked="isCategorySelected(cat.id)"
                                    class="rounded border-slate-600"
                                    @change="toggleCategory(cat.id)"
                                />
                                <label :for="`tpl-cat-${cat.id}`" class="text-sm text-site-body cursor-pointer">{{ cat.name }}</label>
                            </div>
                            <p v-if="categories.length === 0" class="text-sm text-slate-500 py-2">No categories. Create some in Template Categories first.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-lg px-4 py-2 text-site-body hover:bg-slate-700"
                        @click="closeTemplateEdit"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500 disabled:opacity-50"
                        :disabled="templateEditLoading"
                        @click="submitTemplateEdit"
                    >
                        {{ templateEditLoading ? 'Saving…' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
