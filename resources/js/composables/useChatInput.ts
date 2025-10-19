import type { InjectionKey } from 'vue'
import { useStorage } from '@vueuse/core'
import { inject, provide } from 'vue'

interface ChatInputState {
  input: ReturnType<typeof useStorage<string>>
  setInput: (value: string) => void
  clearInput: () => void
}

const CHAT_INPUT_KEY: InjectionKey<ChatInputState> = Symbol('chatInput')

export function provideChatInput() {
  const input = useStorage<string>('chat-input', '')

  const setInput = (value: string): void => {
    input.value = value
  }

  const clearInput = (): void => {
    input.value = ''
  }

  const chatInputState: ChatInputState = {
    input,
    setInput,
    clearInput,
  }

  provide(CHAT_INPUT_KEY, chatInputState)

  return chatInputState
}

export function useChatInput() {
  const chatInputState = inject(CHAT_INPUT_KEY)

  if (!chatInputState) {
    throw new Error(
      'useChatInput must be used within a component that provides chat input',
    )
  }

  return chatInputState
}
