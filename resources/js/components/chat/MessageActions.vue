<script setup lang="ts">
import type { Message } from '@/types'
import { Icon } from '@iconify/vue'
import { useForm } from '@inertiajs/vue3'
import { useClipboard } from '@vueuse/core'
import { computed } from 'vue'
import { toast } from 'vue-sonner'
import { Button } from '@/components/ui/button'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { Role } from '@/types/enum'

const props = defineProps<{
  message: Message
  isLoading: boolean
  chatId?: string
}>()

const { copy, copied } = useClipboard({ legacy: true })

const form = useForm({
  message_id: props.message.id,
  is_upvoted: props.message.is_upvoted,
})

const shouldShowActions = computed(() => !props.isLoading && props.message.role === Role.ASSISTANT)

const isUpvoted = computed(() => form.is_upvoted === true)
const isDownvoted = computed(() => form.is_upvoted === false)

function voteMessage(isUpvote: boolean) {
  const action = isUpvote ? 'upvote' : 'downvote'
  const previousState = form.is_upvoted

  form.is_upvoted = isUpvote

  const promise = new Promise((resolve, reject) => {
    form.patch(route('chats.update', { chat: props.chatId }), {
      async: true,
      onSuccess: () => resolve({ action }),
      onError: () => {
        form.is_upvoted = previousState
        reject(new Error(`Failed to ${action}`))
      },
    })
  })

  toast.promise(promise, {
    loading: `${action === 'upvote' ? 'Upvoting' : 'Downvoting'} response...`,
    success: `Response ${action}d!`,
    error: `Failed to ${action}. Please try again`,
  })
}

const upvoteMessage = () => voteMessage(true)
const downvoteMessage = () => voteMessage(false)

const actionButtons = computed(() => [
  {
    icon: copied.value ? 'lucide:check' : 'lucide:copy',
    tooltip: copied.value ? 'Copied!' : 'Copy',
    onClick: () => copy(props.message.parts || ''),
    class: 'text-muted-foreground',
    disabled: false,
  },
  {
    icon: 'lucide:thumbs-up',
    tooltip: isUpvoted.value ? 'Upvoted' : 'Upvote Response',
    onClick: upvoteMessage,
    class: isUpvoted.value ? 'text-primary' : 'text-muted-foreground',
    disabled: isUpvoted.value || form.processing,
    testId: 'message-upvote',
  },
  {
    icon: 'lucide:thumbs-down',
    tooltip: isDownvoted.value ? 'Downvoted' : 'Downvote Response',
    onClick: downvoteMessage,
    class: isDownvoted.value ? 'text-primary' : 'text-muted-foreground',
    disabled: isDownvoted.value || form.processing,
    testId: 'message-downvote',
  },
])
</script>

<template>
  <div v-if="shouldShowActions" class="flex flex-row gap-2">
    <Tooltip v-for="(action, index) in actionButtons" :key="index">
      <TooltipTrigger as-child>
        <Button
          :data-testid="action.testId"
          class="py-1 px-2 h-fit !pointer-events-auto"
          :class="action.class"
          :disabled="action.disabled"
          variant="outline"
          @click="action.onClick"
        >
          <Icon :icon="action.icon" />
        </Button>
      </TooltipTrigger>
      <TooltipContent>{{ action.tooltip }}</TooltipContent>
    </Tooltip>
  </div>
</template>
