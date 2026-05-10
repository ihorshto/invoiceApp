import { computed } from 'vue'

export function useInvoiceItems(items) {
    const addItem = () => {
        items.value.push({
            product_id:  null,
            description: '',
            unit_price:  0,
            unit:        'unité',
            quantity:    1,
            vat_rate:    20,
        })
    }

    const removeItem = (index) => {
        items.value.splice(index, 1)
    }

    const fillFromProduct = (index, product) => {
        if (!product) return
        items.value[index].description = product.name
        items.value[index].unit_price  = parseFloat(product.unit_price)
        items.value[index].unit        = product.unit
        items.value[index].vat_rate    = parseFloat(product.vat_rate)
    }

    const lineHT = (item) => parseFloat(item.unit_price) * parseFloat(item.quantity)

    const subtotal = computed(() => items.value.reduce((s, i) => s + lineHT(i), 0))
    const vatTotal = computed(() => items.value.reduce((s, i) => s + lineHT(i) * (parseFloat(i.vat_rate) / 100), 0))
    const total    = computed(() => subtotal.value + vatTotal.value)

    const fmt = (v) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(v)

    return { addItem, removeItem, fillFromProduct, lineHT, subtotal, vatTotal, total, fmt }
}
