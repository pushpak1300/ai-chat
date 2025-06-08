import type { ComputedRef } from 'vue'
import type { Message, MessageChunks } from '@/types'
import { computed } from 'vue'
import { ChunkType } from '@/types/enum'

export function useMessageFormatting(message: Message): {
  messageParts: ComputedRef<MessageChunks[]>
  hasThinking: ComputedRef<boolean>
  hasText: ComputedRef<boolean>
} {
  const messageParts = computed<MessageChunks[]>(() => {
    const parts: MessageChunks[] = []

    if (message.parts[ChunkType.THINKING]) {
      parts.push({
        [ChunkType.THINKING]: message.parts[ChunkType.THINKING],
      })
    }

    if (message.parts[ChunkType.TEXT]) {
      parts.push({
        [ChunkType.TEXT]: message.parts[ChunkType.TEXT],
      })
    }

    return parts
  })

  const hasThinking = computed<boolean>(() => !!message.parts[ChunkType.THINKING])
  const hasText = computed<boolean>(() => !!message.parts[ChunkType.TEXT])

  return {
    messageParts,
    hasThinking,
    hasText,
  }
}
