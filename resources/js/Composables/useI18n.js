import { computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

export function useI18n() {
    const page = usePage()

    const locale = computed(() => page.props.locale ?? 'fr')

    const t = (key) => {
        const parts = key.split('.')
        const group = parts[0]
        const rest  = parts.slice(1)

        const translations = page.props.translations
        if (!translations || !translations[group]) return key

        let value = translations[group]
        for (const part of rest) {
            if (value == null || typeof value !== 'object') return key
            value = value[part]
        }
        return value ?? key
    }

    const switchLocale = (newLocale) => {
        router.post(route('language', newLocale), {}, { preserveScroll: true })
    }

    const formatMoney = (amount, currency = 'EUR') => {
        const loc = locale.value === 'uk' ? 'uk-UA' : 'fr-FR'
        return new Intl.NumberFormat(loc, { style: 'currency', currency }).format(amount)
    }

    const formatDate = (date) => {
        const loc = locale.value === 'uk' ? 'uk-UA' : 'fr-FR'
        return new Date(date).toLocaleDateString(loc)
    }

    return { t, locale, switchLocale, formatMoney, formatDate }
}
