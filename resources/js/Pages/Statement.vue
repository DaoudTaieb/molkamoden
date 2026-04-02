<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import MainLayout from '../Layouts/MainLayout.vue';

const props = defineProps({
    fournisseur: Object,
    movements: Array,
    summary: Object,
});

// Pre-calculate running balances for better performance in Vue
const movementsWithBalances = computed(() => {
    let running = parseFloat(props.summary.solde_depart || 0);
    return props.movements.map(m => {
        const credit = parseFloat(m.credit || 0);
        const debit = parseFloat(m.debit || 0);
        running = running + credit - debit;
        return { ...m, cumulative: running };
    });
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('fr-TN', { minimumFractionDigits: 3 }).format(val);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR');
};

const printPage = () => {
    window.print();
};
</script>

<template>
    <MainLayout>
        <Head :title="'Relevé ' + fournisseur.nom" />
        
        <div class="p-4 md:p-8 lg:p-12 print:p-0">
            <header class="flex flex-col xl:flex-row xl:items-end justify-between gap-8 mb-8 md:mb-12 print:mb-8">
                <div class="space-y-4">
                    <Link href="/" class="inline-flex items-center gap-2 text-[#706f6c] text-[10px] font-bold uppercase tracking-widest hover:text-[#ee2b1b] transition-colors print:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        Retour
                    </Link>
                    <div>
                        <h2 class="text-3xl md:text-4xl font-black text-[#1b1b18] tracking-tight mb-2">Relevé de Compte</h2>
                        <div class="flex flex-wrap items-center gap-4 md:gap-6">
                            <span class="flex items-center gap-2">
                                <span class="text-[9px] md:text-[10px] font-bold text-[#706f6c] uppercase tracking-widest">Fournisseur</span>
                                <span class="font-black text-[#1b1b18] text-sm md:text-base">{{ fournisseur.nom }}</span>
                            </span>
                            <span v-if="fournisseur.fournisseurcode" class="flex items-center gap-2">
                                <span class="text-[9px] md:text-[10px] font-bold text-[#706f6c] uppercase tracking-widest">Code</span>
                                <span class="px-2 py-0.5 bg-[#f8f8f7] border border-[#e3e3e0] rounded-md font-mono text-xs md:text-sm uppercase leading-none">{{ fournisseur.fournisseurcode }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 print:hidden self-start xl:self-auto">
                    <button @click="printPage" class="bg-[#1b1b18] text-white px-5 md:px-6 py-2.5 md:py-3 rounded-2xl font-bold flex items-center gap-2 shadow-lg shadow-[#1b1b18]/10 hover:shadow-[#1b1b18]/20 transition-all active:scale-95 text-sm md:text-base">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                        <span class="hidden md:inline">Imprimer</span>
                    </button>
                </div>
            </header>

            <!-- Summary Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-2 md:gap-3 mb-8 md:mb-12 print:mb-8 print:gap-2">
                <div v-for="(val, key) in {
                    'Départ': summary.solde_depart,
                    'Factures': summary.factures,
                    'Bons Entrée': summary.bons_entree,
                    'Règlements': summary.reglements,
                    'Bons Sortie': summary.bons_sortie,
                    'Avoirs': summary.avoirs,
                    'FINAL': summary.solde_final
                }" :key="key" class="bg-white p-2 md:p-4 rounded-xl md:rounded-2xl border border-[#e3e3e0] flex flex-col items-center justify-center text-center shadow-sm">
                    <span class="text-[7px] md:text-[9px] font-bold text-[#706f6c] uppercase tracking-widest mb-0.5 md:mb-1 whitespace-nowrap">{{ key }}</span>
                    <span :class="['font-black text-[10px] md:text-sm tabular-nums truncate w-full px-1', key === 'FINAL' ? (parseFloat(val) > 0 ? 'text-[#ee2b1b]' : parseFloat(val) < 0 ? 'text-[#10b981]' : 'text-[#f59e0b]') : 'text-[#1b1b18]']">
                        {{ formatCurrency(val) }}
                    </span>
                </div>
            </div>

            <!-- Ledger Table -->
            <div class="bg-white rounded-[1.2rem] md:rounded-[2rem] border border-[#e3e3e0] shadow-sm overflow-hidden print:border-none print:shadow-none">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[9px] md:text-[10px] font-bold text-[#706f6c] uppercase tracking-widest bg-[#fdfdfc] border-b border-[#f0f0f0]">
                                <th class="px-3 md:px-8 py-4 md:py-5">Date</th>
                                <th class="px-3 md:px-8 py-4 md:py-5 hidden lg:table-cell">Opération</th>
                                <th class="px-3 md:px-8 py-4 md:py-5">Pièce</th>
                                <th class="px-3 md:px-8 py-4 md:py-5 text-right hidden sm:table-cell">Débit</th>
                                <th class="px-3 md:px-8 py-4 md:py-5 text-right hidden sm:table-cell">Crédit</th>
                                <th class="px-3 md:px-8 py-4 md:py-5 text-right">Solde</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0f0f0]">
                            <!-- Opening Balance -->
                            <tr class="bg-[#fcfcfb]">
                                <td class="px-3 md:px-8 py-4 text-[9px] md:text-sm text-[#706f6c] font-bold">—</td>
                                <td class="px-3 md:px-8 py-4 font-black text-[#1b1b18] text-[8px] md:text-xs hidden lg:table-cell uppercase">INITIAL</td>
                                <td class="px-3 md:px-8 py-4 text-[9px] md:text-sm text-[#706f6c]">SOLDE DÉBUT</td>
                                <td class="px-3 md:px-8 py-4 text-right text-sm hidden sm:table-cell">—</td>
                                <td class="px-3 md:px-8 py-4 text-right text-sm hidden sm:table-cell">—</td>
                                <td class="px-3 md:px-8 py-4 text-right font-black text-[#1b1b18] tabular-nums text-xs md:text-base">
                                    {{ formatCurrency(summary.solde_depart) }}
                                </td>
                            </tr>
                            
                            <!-- List of movements -->
                            <template v-for="(m, index) in movementsWithBalances" :key="index">
                                <tr :class="{'bg-[#fcfcfb]/50': m.details}">
                                    <td class="px-3 md:px-8 py-4 md:py-6 text-[9px] md:text-sm font-medium text-[#706f6c]">{{ formatDate(m.date) }}</td>
                                    <td class="px-3 md:px-8 py-4 md:py-6 hidden lg:table-cell">
                                        <span class="px-2 py-1 rounded-lg bg-[#f8f8f7] text-[9px] md:text-[10px] font-black text-[#1b1b18] uppercase tracking-wider border border-[#e3e3e0] truncate max-w-[100px] block text-center">{{ m.libelle }}</span>
                                    </td>
                                    <td class="px-3 md:px-8 py-4 md:py-6">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-[#1b1b18] text-[10px] md:text-base leading-none">#{{ m.numero }}</span>
                                            <span class="lg:hidden text-[8px] font-bold text-[#706f6c] uppercase mt-1">{{ m.libelle }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 md:px-8 py-4 md:py-6 text-right tabular-nums text-[10px] md:text-sm font-bold text-[#1b1b18] hidden sm:table-cell">{{ m.debit > 0 ? formatCurrency(m.debit) : '—' }}</td>
                                    <td class="px-3 md:px-8 py-4 md:py-6 text-right tabular-nums text-[10px] md:text-sm font-bold text-[#ee2b1b] hidden sm:table-cell">{{ m.credit > 0 ? formatCurrency(m.credit) : '—' }}</td>
                                    <td class="px-3 md:px-8 py-4 md:py-6 text-right font-black tabular-nums tracking-tighter text-[11px] md:text-lg">
                                        <div class="flex flex-col items-end" :class="[
                                            parseFloat(m.cumulative) > 0 ? 'text-[#ee2b1b]' : 
                                            parseFloat(m.cumulative) < 0 ? 'text-[#10b981]' : 
                                            'text-[#f59e0b]'
                                        ]">
                                            <span>{{ formatCurrency(m.cumulative) }}</span>
                                            <div class="sm:hidden flex flex-col items-end mt-1 space-y-0.5">
                                                <span v-if="m.debit > 0" class="text-[8px] font-bold text-[#1b1b18] leading-none">- {{ formatCurrency(m.debit) }}</span>
                                                <span v-if="m.credit > 0" class="text-[8px] font-bold text-[#ee2b1b] leading-none">+ {{ formatCurrency(m.credit) }}</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Automatic Details -->
                                <tr v-if="m.details" class="bg-[#fcfcfb]">
                                    <td colspan="6" class="p-0">
                                        <div class="border-l-4 border-[#ee2b1b] bg-white m-3 md:m-4 md:ml-8 p-4 md:p-8 rounded-2xl shadow-sm border border-[#e3e3e0]">
                                            <div class="flex flex-col sm:flex-row justify-between items-start gap-4 mb-6 md:mb-8">
                                                <div>
                                                    <h4 class="font-black text-lg md:text-xl text-[#1b1b18] mb-1">{{ m.libelle }} #{{ m.numero }}</h4>
                                                    <p class="text-[10px] font-bold text-[#706f6c] uppercase tracking-widest">Réf Interne: {{ m.header?.numerointerne || '—' }}</p>
                                                </div>
                                                <div class="sm:text-right">
                                                    <p class="text-[9px] md:text-[10px] font-bold text-[#706f6c] uppercase tracking-widest mb-1">Total Document</p>
                                                    <p class="text-xl md:text-2xl font-black text-[#ee2b1b]">{{ formatCurrency(m.header?.netapayer) }} TND</p>
                                                </div>
                                            </div>

                                            <div class="overflow-x-auto text-nowrap">
                                                <table class="w-full text-[10px] md:text-xs">
                                                    <thead>
                                                        <tr class="border-b border-[#f0f0f0] text-[#706f6c] font-black uppercase tracking-widest">
                                                            <th class="py-3 text-left">Référence</th>
                                                            <th class="py-3 text-left">Désignation</th>
                                                            <th class="py-3 text-right">Qté</th>
                                                            <th class="py-3 text-right">P.U HT</th>
                                                            <th class="py-3 text-right">Total TTC</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="d in m.details" :key="d.produitid" class="border-b border-[#f8f8f7] last:border-0 hover:bg-[#fafafa]">
                                                            <td class="py-3 font-mono text-[#706f6c] uppercase">{{ d.produitcode }}</td>
                                                            <td class="py-3 font-bold text-[#1b1b18]">{{ d.produitlibelle }}</td>
                                                            <td class="py-3 text-right font-bold">{{ d.qte }}</td>
                                                            <td class="py-3 text-right text-[#706f6c]">{{ formatCurrency(d.ht) }}</td>
                                                            <td class="py-3 text-right font-black text-[#1b1b18]">{{ formatCurrency(d.totalttc) }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="mt-6 md:mt-8 flex flex-wrap justify-end gap-6 md:gap-12 border-t border-[#f0f0f0] pt-6">
                                                <div class="text-right">
                                                    <p class="text-[8px] md:text-[9px] font-bold text-[#706f6c] uppercase tracking-widest leading-none mb-1">Total HT</p>
                                                    <p class="font-bold text-[#1b1b18] text-xs md:text-sm">{{ formatCurrency(m.header?.totalhtnet || m.header?.totalnetht) }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-[8px] md:text-[9px] font-bold text-[#706f6c] uppercase tracking-widest leading-none mb-1">Calcul TVA</p>
                                                    <p class="font-bold text-[#1b1b18] text-xs md:text-sm">{{ formatCurrency(m.header?.totaltva) }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-[8px] md:text-[9px] font-bold text-[#706f6c] uppercase tracking-widest leading-none mb-1">Net à Payer</p>
                                                    <p class="font-black text-lg md:text-xl text-[#ee2b1b]">{{ formatCurrency(m.header?.netapayer) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style>
@media print {
    body { background: white !important; }
    main { padding: 0 !important; margin-left: 0 !important; }
    .bg-white { border: none !important; box-shadow: none !important; }
    .divide-y > * { border-bottom-width: 1px !important; border-color: #eee !important; }
}
</style>
