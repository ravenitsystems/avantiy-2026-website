<script setup>
import ToastContainer from './components/ToastContainer.vue';
</script>

<template>
    <router-view v-slot="{ Component, route }">
        <div :key="route.name">
            <Component :is="Component" />
        </div>
    </router-view>
    <ToastContainer />
</template>

<style scoped>

</style>
