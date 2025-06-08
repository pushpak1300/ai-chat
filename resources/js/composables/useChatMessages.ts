import type { Ref } from 'vue'
import type { Chat, Message, MessageChunks } from '@/types'
import { nextTick, ref, watch } from 'vue'
import { ChunkType, Role } from '@/types/enum'

export function useChatMessages(chat: Chat, chatContainerRef: Ref<any>) {
  const messages = ref<Message[]>([
    ...(chat?.messages?.map(message => ({
      ...message,
      attachments: typeof message.attachments === 'string'
        ? JSON.parse(message.attachments)
        : message.attachments,
    })) || []),
  ])

  const addUserMessage = (content: MessageChunks): Message => {
    const userMessage: Message = {
      role: Role.USER,
      parts: content,
      attachments: [],
    }
    messages.value.push(userMessage)
    return userMessage
  }

  const addTextMessage = (text: string): Message => {
    return addUserMessage({ [ChunkType.TEXT]: text })
  }

  const scrollToBottom = (): void => {
    nextTick(() => {
      if (chatContainerRef.value) {
        chatContainerRef.value.handleScrollToBottom()
      }
    })
  }

  const getLastMessage = (): Message | undefined => {
    return messages.value[messages.value.length - 1]
  }

  const isLastMessageFromUser = (): boolean => {
    const lastMessage = getLastMessage()
    return lastMessage?.role === Role.USER
  }

  watch(() => chat?.messages, (newMessages) => {
    if (newMessages && newMessages.length > 0) {
      messages.value = [...newMessages]
      scrollToBottom()
    }
  }, { immediate: true, deep: true })

  return {
    messages,
    addUserMessage,
    addTextMessage,
    scrollToBottom,
    getLastMessage,
    isLastMessageFromUser,
  }
}
