<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType, ChatHistory } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    chatHistory?: ChatHistory;
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" :chat-history="chatHistory">
        <slot />
    </AppLayout>
</template>
