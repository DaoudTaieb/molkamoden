<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const debounce = (fn, delay) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
};

const props = defineProps({
    stocks: Object,
    filters: Object,
    sites: Array,
    familles: Array,
    sous_familles: Array,
    fournisseurs: Array
});

const search = ref(props.filters.search || '');
const site_id = ref(props.filters.site_id || '');
const famille_id = ref(props.filters.famille_id || '');
const sous_famille_id = ref(props.filters.sous_famille_id || '');
const fournisseur_id = ref(props.filters.fournisseur_id || '');

const filter = () => {
    router.get('/stock', {
        search: search.value,
        site_id: site_id.value,
        famille_id: famille_id.value,
        sous_famille_id: sous_famille_id.value,
        fournisseur_id: fournisseur_id.value
    }, {
        preserveState: true,
        replace: true
    });
};

watch([site_id, famille_id, sous_famille_id, fournisseur_id], () => {
    filter();
});

watch(search, debounce(() => {
    filter();
}, 500));

const clearFilters = () => {
    search.value = '';
    site_id.value = '';
    famille_id.value = '';
    sous_famille_id.value = '';
    fournisseur_id.value = '';
};
</script>

<template>
    <Head title="Gestion du Stock" />

    <MainLayout>
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">État du Stock</h1>
                <button @click="clearFilters" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    Réinitialiser les filtres
                </button>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="lg:col-span-1">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Recherche</label>
                    <input v-model="search" type="text" placeholder="Code ou Désignation..." 
                        class="w-full border-gray-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Site/Dépôt</label>
                    <select v-model="site_id" class="w-full border-gray-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous les sites</option>
                        <option v-for="site in sites" :key="site.id" :value="site.id">{{ site.libelle }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Famille</label>
                    <select v-model="famille_id" class="w-full border-gray-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Toutes les familles</option>
                        <option v-for="f in familles" :key="f.id" :value="f.id">{{ f.libelle }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Sous-Famille</label>
                    <select v-model="sous_famille_id" class="w-full border-gray-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Toutes les sous-familles</option>
                        <option v-for="sf in sous_familles" :key="sf.id" :value="sf.id">{{ sf.libelle }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Fournisseur</label>
                    <select v-model="fournisseur_id" class="w-full border-gray-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous les fournisseurs</option>
                        <option v-for="fn in fournisseurs" :key="fn.id" :value="fn.id">{{ fn.libelle }}</option>
                    </select>
                </div>
            </div>

            <!-- Stock Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-4 md:px-6 py-4 text-[10px] md:text-xs font-bold text-gray-500 uppercase tracking-wider">Produit</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider hidden md:table-cell">Famille</th>
                                <th class="px-4 md:px-6 py-4 text-[10px] md:text-xs font-bold text-gray-500 uppercase tracking-wider">Site</th>
                                <th class="px-4 md:px-6 py-4 text-[10px] md:text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Stock</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="item in stocks.data" :key="item.stockid" class="hover:bg-blue-50/30 transition-colors">
                                <td class="px-4 md:px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] md:text-xs font-mono text-gray-400 mb-0.5">{{ item.produitcode }}</span>
                                        <span class="text-xs md:text-sm font-bold text-gray-800 line-clamp-2">{{ item.produitlibelle }}</span>
                                        <span class="text-[10px] text-gray-500 mt-1 md:hidden">{{ item.fournisseur }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 hidden md:table-cell">
                                    <div class="flex flex-col">
                                        <span class="px-2 py-0.5 bg-gray-100 rounded text-[10px] inline-block self-start">{{ item.famille }}</span>
                                        <span v-if="item.sous_famille" class="text-[10px] text-gray-400 mt-1">{{ item.sous_famille }}</span>
                                    </div>
                                </td>
                                <td class="px-4 md:px-6 py-4 text-[10px] md:text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <div class="w-1.5 h-1.5 rounded-full bg-blue-400 mr-1.5"></div>
                                        {{ item.site_libelle }}
                                    </div>
                                </td>
                                <td class="px-4 md:px-6 py-4 text-sm md:text-base font-bold text-right tabular-nums" :class="item.qtestock > 0 ? 'text-green-600' : 'text-red-500'">
                                    {{ parseFloat(item.qtestock).toFixed(2) }}
                                </td>
                            </tr>
                            <tr v-if="stocks.data.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic text-sm">
                                    Aucun produit trouvé en stock avec ces critères.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="stocks.links.length > 3" class="mt-6 flex justify-center">
                <nav class="flex gap-1">
                    <template v-for="(link, k) in stocks.links" :key="k">
                        <div v-if="link.url === null" 
                             class="px-4 py-2 text-sm text-gray-400 bg-white border border-gray-200 rounded-lg"
                             v-html="link.label" />
                        <Link v-else 
                              :href="link.url"
                              class="px-4 py-2 text-sm bg-white border rounded-lg transition-colors"
                              :class="link.active ? 'text-white bg-blue-600 border-blue-600 font-bold' : 'text-gray-600 border-gray-200 hover:bg-gray-50'"
                              v-html="link.label" />
                    </template>
                </nav>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
/* Optional: specific styles for the stock page */
</style>
