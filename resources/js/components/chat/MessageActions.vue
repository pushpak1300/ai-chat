<template>
  <div v-if="!isLoading && message.role === Role.ASSISTANT" class="flex flex-row gap-2">
    <Tooltip>
      <TooltipTrigger as-child>
        <Button
          class="py-1 px-2 h-fit text-muted-foreground"
          variant="outline"
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
          data-testid="message-upvote"
          class="py-1 px-2 h-fit text-muted-foreground !pointer-events-auto"
          :disabled="vote?.isUpvoted"
          variant="outline"
          @click="upvoteMessage"
        >
          <Icon icon="lucide:thumbs-up" />
        </Button>
      </TooltipTrigger>
      <TooltipContent>Upvote Response</TooltipContent>
    </Tooltip>

    <Tooltip>
      <TooltipTrigger as-child>
        <Button
          data-testid="message-downvote"
          class="py-1 px-2 h-fit text-muted-foreground !pointer-events-auto"
          variant="outline"
          :disabled="vote && !vote.isUpvoted"
          @click="downvoteMessage"
        >
          <Icon icon="lucide:thumbs-down" />
        </Button>
      </TooltipTrigger>
      <TooltipContent>Downvote Response</TooltipContent>
    </Tooltip>
  </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { Icon } from '@iconify/vue'
import { type Message } from '@/types'
import { Role } from '@/types/enum';
import { useClipboard } from '@vueuse/core'

const props = defineProps<{
    message: Message
    vote?: any
    isLoading: boolean
}>()

const { copy, copied } = useClipboard({ legacy: true })

const upvoteMessage = async () => {
  try {
    const response = await fetch('/api/vote', {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        messageId: props.message.id,
        type: 'up'
      })
    })

    if (response.ok) {
      console.log('Upvoted Response!')
    } else {
      console.error('Failed to upvote response.')
    }
  } catch (error) {
    console.error('Failed to upvote response:', error)
  }
}

const downvoteMessage = async () => {
  try {
    const response = await fetch('/api/vote', {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        messageId: props.message.id,
        type: 'down'
      })
    })

    if (response.ok) {
      console.log('Downvoted Response!')
    } else {
      console.error('Failed to downvote response.')
    }
  } catch (error) {
    console.error('Failed to downvote response:', error)
  }
}
</script>
