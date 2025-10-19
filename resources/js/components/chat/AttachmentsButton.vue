<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { useStream } from '@laravel/stream-vue'
import { Button } from '@/components/ui/button'
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip'

interface Props {
  fileInputRef: HTMLInputElement | undefined
  chatId?: string
  streamId?: string
}

const props = defineProps<Props>()

const { isStreaming } = useStream(`stream/${props.chatId}`, {
  id: props.streamId,
})

function handleClick() {
  if (props.fileInputRef) {
    props.fileInputRef.click()
  }
}
</script>

<template>
  <Tooltip>
    <TooltipTrigger as-child>
      <Button
        variant="ghost"
        size="icon"
        class="h-8 w-8 rounded-full"
        :disabled="isStreaming"
        @click="handleClick"
      >
        <Icon icon="lucide:paperclip" class="size-4" />
      </Button>
    </TooltipTrigger>
    <TooltipContent>Attach files</TooltipContent>
  </Tooltip>
</template>
