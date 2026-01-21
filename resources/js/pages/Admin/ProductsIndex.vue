<script setup>
import ProductList from '@/Components/ProductList.vue'
import { useAuth } from '@/composables/useAuth'
import { onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const { isAuthenticated } = useAuth()

// Защита маршрута
onMounted(() => {
    if (!isAuthenticated.value) {
        router.visit('/login')
    }
})
</script>

<template>
    <ProductList :is-admin="true" />
</template>
