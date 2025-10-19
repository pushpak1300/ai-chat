<script setup lang="ts">
import type { Message } from '@/types'
import { Icon } from '@iconify/vue'
import { useClipboard } from '@vueuse/core'
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip'
import { useAuth } from '@/composables/useAuth'
import { useMessageVoting } from '@/composables/useMessageVoting'
import { Role } from '@/types/enum'

interface Props {
  message: Message
  isLoading: boolean
  chatId?: string
}

const props = defineProps<Props>()

const { isGuest } = useAuth()
const { copy, copied } = useClipboard({ legacy: true })
const { isUpvoted, isDownvoted, isProcessing, upvoteMessage, downvoteMessage }
  = useMessageVoting(props.message, props.chatId)

const shouldShowActions = computed(
  () => !props.isLoading && props.message.role === Role.ASSISTANT,
)

const handleCopy = () => copy(props.message.parts.text || '')
const handleUpvote = () => upvoteMessage()
const handleDownvote = () => downvoteMessage()
</script>

<template>
  <div v-if="shouldShowActions" class="flex flex-row gap-2">
    <Tooltip>
      <TooltipTrigger as-child>
        <Button
          class="py-1 px-2 h-fit !pointer-events-auto text-muted-foreground"
          variant="outline"
          @click="handleCopy"
        >
          <Icon :icon="copied ? 'lucide:check' : 'lucide:copy'" />
        </Button>
      </TooltipTrigger>
      <TooltipContent>{{ copied ? "Copied!" : "Copy" }}</TooltipContent>
    </Tooltip>

    <template v-if="!isGuest">
      <Tooltip>
        <TooltipTrigger as-child>
          <Button
            data-testid="message-upvote"
            class="py-1 px-2 h-fit !pointer-events-auto"
            :class="
              isUpvoted ? 'text-primary' : 'text-muted-foreground'
            "
            :disabled="isUpvoted || isProcessing"
            variant="outline"
            @click="handleUpvote"
          >
            <Icon icon="lucide:thumbs-up" />
          </Button>
        </TooltipTrigger>
        <TooltipContent>
          {{ isUpvoted ? "Upvoted" : "Upvote Response" }}
        </TooltipContent>
      </Tooltip>

      <Tooltip>
        <TooltipTrigger as-child>
          <Button
            data-testid="message-downvote"
            class="py-1 px-2 h-fit !pointer-events-auto"
            :class="
              isDownvoted
                ? 'text-primary'
                : 'text-muted-foreground'
            "
            :disabled="isDownvoted || isProcessing"
            variant="outline"
            @click="handleDownvote"
          >
            <Icon icon="lucide:thumbs-down" />
          </Button>
        </TooltipTrigger>
        <TooltipContent>
          {{ isDownvoted ? "Downvoted" : "Downvote Response" }}
        </TooltipContent>
      </Tooltip>
    </template>
  </div>
</template>
