import type { Ref } from 'vue'
import type { Message, StreamEvent } from '@/types'
import { useStream } from '@laravel/stream-vue'
import { nextTick } from 'vue'
import { ContentType, Role, StreamEventType } from '@/types/enum'

interface StreamParams {
  message: string
  model: string
}

export function useMessageStream(
  chatId: string,
  messages: Ref<Message[]>,
  onComplete?: () => void,
) {
  const updateMessageWithEvent = (eventData: StreamEvent): void => {
    let currentMessage = messages.value[messages.value.length - 1]

    if (!currentMessage || currentMessage.role !== Role.ASSISTANT) {
      currentMessage = {
        role: Role.ASSISTANT,
        parts: {},
      }
      messages.value.push(currentMessage)
    }

    const contentType
      = eventData.eventType === StreamEventType.TEXT_DELTA
        ? ContentType.TEXT
        : ContentType.THINKING

    if (!currentMessage.parts[contentType]) {
      currentMessage.parts[contentType] = ''
    }

    currentMessage.parts[contentType] += eventData.content
  }
  const parseStreamChunk = (chunk: string): void => {
    const lines = chunk
      .trim()
      .split('\n')
      .filter(line => line.trim())

    for (const line of lines) {
      try {
        const eventData = JSON.parse(line) as StreamEvent
        if (eventData.eventType !== StreamEventType.ERROR) {
          updateMessageWithEvent(eventData)
        }
      }
      catch (error) {
        console.error(
          'Failed to parse JSON line:',
          error,
          'Line:',
          line,
        )
      }
    }
  }

  const handleStreamError = (): void => {
    nextTick(() => {
      messages.value.push({
        role: Role.ASSISTANT,
        parts: {
          [ContentType.TEXT]:
                        'Sorry, there was an error processing your request. Please try again.',
        },
      })
    })
  }

  const stream = useStream<StreamParams, StreamEvent>(
    route('chat.stream', { chat: chatId }),
    {
      onData: parseStreamChunk,
      onError: handleStreamError,
      onFinish: onComplete,
    },
  )

  return {
    ...stream,
    updateMessageWithEvent,
  }
}
