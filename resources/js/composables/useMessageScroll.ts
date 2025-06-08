import type { Message } from '@/types'
import { nextTick, ref, watch, type Ref } from 'vue'
import { useScrollToBottom } from '@/composables/useScrollToBottom'

export function useMessageScroll(messages: Ref<Message[]>, isStreaming: Ref<boolean>) {
  const {
    containerRef,
    isAtBottom,
    scrollToBottomInstant,
  } = useScrollToBottom()

  const hasSentMessage = ref(false)
  let scrollTimeout: ReturnType<typeof setTimeout> | null = null

  const clearScrollTimeout = (): void => {
    if (scrollTimeout) {
      clearTimeout(scrollTimeout)
      scrollTimeout = null
    }
  }

  const scheduleScroll = (): void => {
    clearScrollTimeout()
    scrollTimeout = setTimeout(() => {
      scrollToBottomInstant()
    }, 100)
  }

  watch(() => messages.value.length, (newLength, oldLength) => {
    if (newLength > oldLength) {
      hasSentMessage.value = true
      if (isAtBottom.value || newLength === 1) {
        nextTick(() => {
          scrollToBottomInstant()
        })
      }
    }
  })

  watch(() => messages.value[messages.value.length - 1]?.parts, () => {
    if (isAtBottom.value && isStreaming.value) {
      scheduleScroll()
    }
  }, { flush: 'post' })

  const scrollToBottomIfNeeded = (): void => {
    if (messages.value.length > 0) {
      nextTick(() => {
        scrollToBottomInstant()
      })
    }
  }

  return {
    containerRef,
    isAtBottom,
    hasSentMessage,
    scrollToBottomInstant,
    scrollToBottomIfNeeded,
    clearScrollTimeout,
  }
}
