<script setup lang="ts">
import type { BreadcrumbItemType, SharedData } from '@/types'
import { usePage } from '@inertiajs/vue3'
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import ChatAction from '@/components/chat/ChatAction.vue'
import ModelSelector from '@/components/chat/ModelSelector.vue'
import VisibilitySelector from '@/components/chat/VisibilitySelector.vue'
import { SidebarTrigger } from '@/components/ui/sidebar'
import { useAuth } from '@/composables/useAuth'

withDefaults(
  defineProps<{
    breadcrumbs?: BreadcrumbItemType[]
  }>(),
  {
    breadcrumbs: () => [],
  },
)

const page = usePage<SharedData>()
const { isGuest } = useAuth()
</script>

<template>
  <header
    class="flex h-16 shrink-0 items-center justify-between gap-2 border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
  >
    <div class="flex items-center gap-2">
      <SidebarTrigger class="-ml-1" />
      <template v-if="breadcrumbs && breadcrumbs.length > 0">
        <Breadcrumbs :breadcrumbs="breadcrumbs" />
      </template>
    </div>
    <div
      v-if="page.url.startsWith('/chat') && !isGuest"
      class="items-center gap-2 hidden md:flex"
    >
      <ChatAction />
      <ModelSelector />
      <VisibilitySelector />
    </div>
  </header>
</template>
