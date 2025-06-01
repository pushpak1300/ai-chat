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

<script setup lang="ts">
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { Icon } from '@iconify/vue'

interface Props {
  input: string
  uploadQueue: Array<string>
  isProcessing?: boolean
}

const props = defineProps<Props>()

defineEmits<{
  submit: []
}>()

const canSend = computed(() => {
  return props.input.trim().length > 0 && !props.isProcessing
})
</script>
