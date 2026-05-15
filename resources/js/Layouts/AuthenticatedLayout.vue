<!-- resources/js/Layouts/AuthenticatedLayout.vue -->
<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'

const { t, locale, switchLocale } = useI18n()
const drawerOpen = ref(false)

const navItems = [
    { label: 'ui.nav.dashboard', route: 'dashboard',       match: 'dashboard',    icon: '📊' },
    { label: 'ui.nav.invoices',  route: 'invoices.index',  match: 'invoices.*',   icon: '📄' },
    { label: 'ui.nav.clients',   route: 'clients.index',   match: 'clients.*',    icon: '👥' },
    { label: 'ui.nav.products',  route: 'products.index',  match: 'products.*',   icon: '📦' },
    { label: 'ui.nav.settings',  route: 'settings.company', match: 'settings.*',  icon: '🏢' },
]
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-white font-sans antialiased">

        <!-- SIDEBAR (desktop) -->
        <aside class="hidden lg:flex w-[220px] flex-col border-r border-mint-100 bg-mint-50 flex-shrink-0">
            <!-- Logo -->
            <div class="flex items-center gap-2 border-b border-mint-100 px-4 py-[18px]">
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-md bg-mint-500 text-xs font-bold text-white">I</div>
                    <span class="text-[13px] font-bold text-mint-900">InvoiceApp</span>
                </Link>
            </div>

            <!-- Nav -->
            <nav class="flex-1 px-2 py-3">
                <p class="mb-1 mt-2 px-2 text-[10px] font-semibold uppercase tracking-widest text-mint-600">
                    {{ t('ui.nav.group.main') }}
                </p>
                <Link
                    v-for="item in navItems"
                    :key="item.route"
                    :href="route(item.route)"
                    class="mb-0.5 flex items-center gap-2 rounded-md px-2.5 py-[7px] text-[13px] font-medium transition-colors"
                    :class="route().current(item.match)
                        ? 'bg-mint-500 text-white font-semibold'
                        : 'text-mint-600 hover:bg-mint-100'"
                >
                    <span class="w-[18px] text-center text-[15px]">{{ item.icon }}</span>
                    {{ t(item.label) }}
                </Link>
            </nav>

            <!-- Footer -->
            <div class="border-t border-mint-100 px-2 py-3 space-y-1">
                <!-- Language switcher -->
                <div class="flex items-center gap-2 px-2.5 py-1">
                    <button @click="switchLocale('fr')" class="text-xs font-semibold" :class="locale === 'fr' ? 'text-mint-600' : 'text-slate-400 hover:text-slate-600'">FR</button>
                    <span class="text-slate-300 text-xs">|</span>
                    <button @click="switchLocale('uk')" class="text-xs font-semibold" :class="locale === 'uk' ? 'text-mint-600' : 'text-slate-400 hover:text-slate-600'">UK</button>
                </div>
                <!-- User chip -->
                <div class="flex items-center gap-2 rounded-md px-2.5 py-2">
                    <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-mint-500 text-[11px] font-bold text-white">
                        {{ $page.props.auth.user.name.slice(0,2).toUpperCase() }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-xs font-semibold text-mint-900">{{ $page.props.auth.user.name }}</p>
                        <p class="truncate text-[10px] text-slate-500">{{ $page.props.auth.user.email }}</p>
                    </div>
                </div>
                <!-- Logout -->
                <Link :href="route('logout')" method="post" as="button" class="w-full text-left px-2.5 py-1.5 text-xs text-slate-500 hover:text-red-500 rounded-md hover:bg-red-50 transition-colors">
                    {{ t('ui.nav.logout') }}
                </Link>
            </div>
        </aside>

        <!-- MOBILE DRAWER OVERLAY -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="drawerOpen" class="fixed inset-0 z-40 bg-black/30 lg:hidden" @click="drawerOpen = false" />
        </Transition>

        <!-- MOBILE DRAWER PANEL -->
        <Transition
            enter-active-class="transition-transform duration-200"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-200"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <aside v-if="drawerOpen" class="fixed inset-y-0 left-0 z-50 flex w-[220px] flex-col border-r border-mint-100 bg-mint-50 lg:hidden">
                <div class="flex items-center gap-2 border-b border-mint-100 px-4 py-[18px]">
                    <div class="flex h-7 w-7 items-center justify-center rounded-md bg-mint-500 text-xs font-bold text-white">I</div>
                    <span class="text-[13px] font-bold text-mint-900">InvoiceApp</span>
                    <button class="ml-auto text-slate-400 hover:text-slate-600" @click="drawerOpen = false">✕</button>
                </div>
                <nav class="flex-1 px-2 py-3">
                    <Link
                        v-for="item in navItems"
                        :key="item.route"
                        :href="route(item.route)"
                        class="mb-0.5 flex items-center gap-2 rounded-md px-2.5 py-[7px] text-[13px] font-medium transition-colors"
                        :class="route().current(item.match)
                            ? 'bg-mint-500 text-white font-semibold'
                            : 'text-mint-600 hover:bg-mint-100'"
                        @click="drawerOpen = false"
                    >
                        <span class="w-[18px] text-center text-[15px]">{{ item.icon }}</span>
                        {{ t(item.label) }}
                    </Link>
                </nav>
                <div class="border-t border-mint-100 px-2 py-3">
                    <div class="flex items-center gap-2 px-2.5 py-1">
                        <button @click="switchLocale('fr')" class="text-xs font-semibold" :class="locale === 'fr' ? 'text-mint-600' : 'text-slate-400'">FR</button>
                        <span class="text-slate-300 text-xs">|</span>
                        <button @click="switchLocale('uk')" class="text-xs font-semibold" :class="locale === 'uk' ? 'text-mint-600' : 'text-slate-400'">UK</button>
                    </div>
                    <Link :href="route('logout')" method="post" as="button" class="w-full text-left px-2.5 py-1.5 text-xs text-slate-500 hover:text-red-500">
                        {{ t('ui.nav.logout') }}
                    </Link>
                </div>
            </aside>
        </Transition>

        <!-- MAIN AREA -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Mobile topbar -->
            <div class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 lg:hidden">
                <button @click="drawerOpen = true" class="flex flex-col gap-1 p-1">
                    <span class="h-0.5 w-5 bg-mint-900 rounded"></span>
                    <span class="h-0.5 w-5 bg-mint-900 rounded"></span>
                    <span class="h-0.5 w-5 bg-mint-900 rounded"></span>
                </button>
                <span class="text-sm font-bold text-mint-900">InvoiceApp</span>
                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-mint-500 text-[11px] font-bold text-white">
                    {{ $page.props.auth.user.name.slice(0,2).toUpperCase() }}
                </div>
            </div>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto bg-white">
                <div v-if="$slots.header" class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <slot name="header" />
                </div>
                <slot />
            </main>
        </div>

    </div>
</template>
