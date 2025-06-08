import type { Ref } from 'vue'
import type { Chunk, Message } from '@/types'
import { useStream } from '@laravel/stream-vue'
import { nextTick } from 'vue'
import { ChunkType, Role } from '@/types/enum'

interface StreamParams {
  message: string
  model: string
}

export function useMessageStream(chatId: string, messages: Ref<Message[]>, onComplete?: () => void) {
  const updateMessageWithChunk = (chunkData: Chunk): void => {
    let currentMessage = messages.value[messages.value.length - 1]

    if (!currentMessage || currentMessage.role !== Role.ASSISTANT) {
      currentMessage = {
        role: Role.ASSISTANT,
        parts: {},
      }
      messages.value.push(currentMessage)
    }

    if (!currentMessage.parts[chunkData.chunkType]) {
      currentMessage.parts[chunkData.chunkType] = ''
    }

    currentMessage.parts[chunkData.chunkType] += chunkData.content
  }
  const parseStreamChunk = (chunk: string): void => {
    const lines = chunk.trim().split('\n').filter(line => line.trim())

    for (const line of lines) {
      try {
        const chunkData = JSON.parse(line) as Chunk
        updateMessageWithChunk(chunkData)
      }
      catch (error) {
        console.error('Failed to parse JSON line:', error, 'Line:', line)
      }
    }
  }

  const handleStreamError = (): void => {
    nextTick(() => {
      messages.value.push({
        role: Role.ASSISTANT,
        parts: {
          [ChunkType.TEXT]: 'Sorry, there was an error processing your request. Please try again.',
        },
      })
    })
  }

  const stream = useStream<StreamParams, Chunk>(
    route('chat.stream', { chat: chatId }),
    {
      onData: parseStreamChunk,
      onError: handleStreamError,
      onFinish: onComplete,
    },
  )

  return {
    ...stream,
    updateMessageWithChunk,
  }
}
