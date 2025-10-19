import type { ComputedRef } from 'vue'
import type { Message, MessageParts } from '@/types'
import { computed } from 'vue'
import { ContentType } from '@/types/enum'

export function useMessageFormatting(message: Message): {
  messageParts: ComputedRef<MessageParts[]>
  hasThinking: ComputedRef<boolean>
  hasText: ComputedRef<boolean>
} {
  const messageParts = computed<MessageParts[]>(() => {
    const parts: MessageParts[] = []

    if (message.parts[ContentType.THINKING]) {
      parts.push({
        [ContentType.THINKING]: message.parts[ContentType.THINKING],
      })
    }

    if (message.parts[ContentType.TEXT]) {
      parts.push({
        [ContentType.TEXT]: message.parts[ContentType.TEXT],
      })
    }

    return parts
  })

  const hasThinking = computed<boolean>(
    () => !!message.parts[ContentType.THINKING],
  )
  const hasText = computed<boolean>(() => !!message.parts[ContentType.TEXT])

  return {
    messageParts,
    hasThinking,
    hasText,
  }
}
