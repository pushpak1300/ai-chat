<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { Button } from '@/components/ui/button'
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip'

const emit = defineEmits<{
  stop: []
}>()

function handleStop() {
  emit('stop')
}
</script>

<template>
  <Tooltip>
    <TooltipTrigger as-child>
      <Button
        variant="outline"
        size="icon"
        class="h-8 w-8 rounded-full border-muted-foreground/25"
        @click="handleStop"
      >
        <Icon icon="lucide:square" class="w-4 h-4" />
      </Button>
    </TooltipTrigger>
    <TooltipContent>Stop generating</TooltipContent>
  </Tooltip>
</template>
