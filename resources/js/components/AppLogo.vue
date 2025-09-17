<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLogoIcon from '@/components/AppLogoIcon.vue'

const page = usePage()
const appName = computed(() => page.props.name)
</script>

<template>
  <div class="flex aspect-square size-8 items-center justify-center rounded-md text-sidebar-primary">
    <AppLogoIcon class="fill-current" />
  </div>
  <div class="grid flex-1 text-left text-sm">
    <span class="truncate font-semibold leading-none">{{ appName }}</span>
  </div>
</template>
