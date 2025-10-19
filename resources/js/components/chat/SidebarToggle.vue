<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { Button } from '@/components/ui/button'
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip'

defineEmits<{
  (e: 'toggleSidebar'): void
}>()
</script>

<template>
  <Tooltip>
    <TooltipTrigger as-child>
      <Button
        data-testid="sidebar-toggle-button"
        variant="outline"
        class="md:px-2 md:h-fit"
        @click="$emit('toggleSidebar')"
      >
        <Icon icon="lucide:panel-left" class="size-4" />
      </Button>
    </TooltipTrigger>
    <TooltipContent align="start">
      Toggle Sidebar
    </TooltipContent>
  </Tooltip>
</template>
