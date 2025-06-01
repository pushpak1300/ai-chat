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

<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { Icon } from '@iconify/vue'
import { type Message } from '@/types'

const emit = defineEmits<{
  stop: []
  setMessages: [messages: Array<Message>]
}>()

const handleStop = () => {
  emit('stop')
}
</script>
