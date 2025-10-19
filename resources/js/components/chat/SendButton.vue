<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip'
import { useChatInput } from '@/composables/useChatInput'

const props = withDefaults(
  defineProps<{
    uploadQueue: Array<string>
    isProcessing?: boolean
  }>(),
  {
    isProcessing: false,
  },
)

defineEmits<{
  submit: []
}>()

const { input } = useChatInput()

const canSend = computed(() => {
  return input.value.trim().length > 0 && !props.isProcessing
})
</script>

<template>
  <Tooltip>
    <TooltipTrigger as-child>
      <Button
        type="submit"
        size="icon"
        class="h-8 w-8 rounded-full"
        :disabled="!canSend"
        @click="$emit('submit')"
      >
        <Icon v-if="!props.isProcessing" icon="lucide:arrow-up" />
        <Icon v-else icon="lucide:loader-2" class="animate-spin" />
      </Button>
    </TooltipTrigger>
    <TooltipContent>Send message</TooltipContent>
  </Tooltip>
</template>
