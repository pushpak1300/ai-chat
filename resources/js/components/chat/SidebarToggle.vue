<template>
  <Tooltip>
    <TooltipTrigger as-child>
      <Button
        data-testid="sidebar-toggle-button"
        variant="outline"
        class="md:px-2 md:h-fit"
        @click="toggleSidebar"
      >
        <Icon icon="lucide:panel-left" class="size-4" />
      </Button>
    </TooltipTrigger>
    <TooltipContent align="start">Toggle Sidebar</TooltipContent>
  </Tooltip>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { Icon } from '@iconify/vue'

const toggleSidebar = () => {
  // Implement sidebar toggle logic
  console.log('Toggle sidebar')
}
</script>
