<script setup lang="ts">
import type { Message } from '@/types'
import { Icon } from '@iconify/vue'
import { useForm } from '@inertiajs/vue3'
import { useClipboard } from '@vueuse/core'
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

function upvoteMessage() {
  form.is_upvoted = true
  form.patch(route('chats.update', { chat: props.chatId }), {
    onSuccess: () => {
      form.is_upvoted = true
      toast.success('Response upvoted!')
    },
    onError: () => {
      form.is_upvoted = props.message.is_upvoted
      toast.error('Failed to upvote', {
        description: 'Please try again',
      })
    },
  })
}

function downvoteMessage() {
  form.is_upvoted = false
  form.patch(route('chats.update', { chat: props.chatId }), {
    onSuccess: () => {
      form.is_upvoted = false
      toast.success('Response downvoted!')
    },
    onError: () => {
      form.is_upvoted = props.message.is_upvoted
      toast.error('Failed to downvote')
    },
  })
}
</script>

<template>
  <div v-if="!isLoading && message.role === Role.ASSISTANT" class="flex flex-row gap-2">
    <Tooltip>
      <TooltipTrigger as-child>
        <Button
          class="py-1 px-2 h-fit text-muted-foreground" variant="outline"
          @click="copy(message.parts || '')"
        >
          <Icon :icon="copied ? 'lucide:check' : 'lucide:copy'" />
        </Button>
      </TooltipTrigger>
      <TooltipContent>{{ copied ? 'Copied!' : 'Copy' }}</TooltipContent>
    </Tooltip>

    <Tooltip>
      <TooltipTrigger as-child>
        <Button
          data-testid="message-upvote" class="py-1 px-2 h-fit !pointer-events-auto"
          :class="form.is_upvoted === true ? 'text-primary' : 'text-muted-foreground'"
          :disabled="form.is_upvoted === true || form.processing" variant="outline" @click="upvoteMessage"
        >
          <Icon icon="lucide:thumbs-up" />
        </Button>
      </TooltipTrigger>
      <TooltipContent>{{ message.is_upvoted === true ? 'Upvoted' : 'Upvote Response' }}</TooltipContent>
    </Tooltip>

    <Tooltip>
      <TooltipTrigger as-child>
        <Button
          data-testid="message-downvote" class="py-1 px-2 h-fit !pointer-events-auto"
          :class="form.is_upvoted === false ? 'text-primary' : 'text-muted-foreground'"
          :disabled="form.is_upvoted === false || form.processing" variant="outline" @click="downvoteMessage"
        >
          <Icon icon="lucide:thumbs-down" />
        </Button>
      </TooltipTrigger>
      <TooltipContent>{{ message.is_upvoted === false ? 'Downvoted' : 'Downvote Response' }}</TooltipContent>
    </Tooltip>
  </div>
</template>
