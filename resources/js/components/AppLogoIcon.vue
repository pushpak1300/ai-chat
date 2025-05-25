<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Icon } from "@iconify/vue";

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <Icon icon="lucide:message-circle" class="size-5" />
</template>
