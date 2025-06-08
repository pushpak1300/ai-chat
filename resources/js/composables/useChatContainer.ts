import type { Message } from '@/types'
import { ref, type Ref } from 'vue'

export function useChatContainer() {
  const isAtBottom = ref(false)
  const messagesRef = ref<any>()

  const handleScrollToBottom = (): void => {
    messagesRef.value?.scrollToBottom()
  }

  const updateIsAtBottom = (value: boolean): void => {
    isAtBottom.value = value
  }

  return {
    isAtBottom,
    messagesRef,
    handleScrollToBottom,
    updateIsAtBottom,
  }
}
