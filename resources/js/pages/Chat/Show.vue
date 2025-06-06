<script setup lang="ts">
import type { BreadcrumbItemType, Chat, ChatHistory, Chunk, Message, MessageChunks, Model } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useJsonStream } from '@laravel/stream-vue'
import { useStorage } from '@vueuse/core'
import { computed, nextTick, onMounted, provide, ref, watch } from 'vue'
import ChatContainer from '@/components/chat/ChatContainer.vue'
import { provideChatInput } from '@/composables/useChatInput'
import { provideVisibility } from '@/composables/useVisibility'
import { MODEL_KEY } from '@/constants/models'
import AppLayout from '@/layouts/AppLayout.vue'
import { ChunkType, Role, Visibility } from '@/types/enum'

const props = defineProps<{
  chatHistory?: ChatHistory
  chat: Chat
  availableModels: Model[]
}>()

const pageTitle = computed<string>(() => props.chat?.title || 'Chat')
const initialVisibility = computed<Visibility>(() => props.chat?.visibility || Visibility.PRIVATE)

const breadcrumbs: BreadcrumbItemType[] = [
  {
    title: 'Chat',
    href: route('chats.index'),
  },
]

interface StreamParams {
  message: string
  model: string
}

const { input, clearInput } = provideChatInput()
const initialVisibilityType = ref<Visibility>(initialVisibility.value)
const selectedModel = useStorage<Model>(MODEL_KEY, props.availableModels[0])
const messages = ref<Message[]>([...(props.chat?.messages?.map(message => ({
  ...message,
  attachments: typeof message.attachments === 'string' ? JSON.parse(message.attachments) : message.attachments,
})) || [])])
const chatContainerRef = ref<InstanceType<typeof ChatContainer>>()

const { visibility } = provideVisibility(initialVisibility.value, initialVisibilityType)

provide('chatId', props.chat.id)

watch(() => props.chat?.messages, (newMessages) => {
  if (newMessages && newMessages.length > 0) {
    messages.value = [...newMessages]
    nextTick(() => {
      if (chatContainerRef.value) {
        chatContainerRef.value.handleScrollToBottom()
      }
    })
  }
}, { immediate: true, deep: true })

function updateChatVisibility(newVisibility: Visibility): void {
  router.patch(
    route('chats.update', { chat: props.chat.id }),
    { visibility: newVisibility },
    {
      preserveState: true,
      preserveScroll: true,
      async: true,
      only: [],
    },
  )
}

watch(visibility, (newVisibility, oldVisibility) => {
  if (oldVisibility !== undefined && newVisibility !== oldVisibility) {
    updateChatVisibility(newVisibility)
  }
}, { immediate: false })

const { isFetching, isStreaming, send, cancel, id } = useJsonStream<Chunk>(
  route('chat.stream', { chat: props.chat.id }),
  {
    onData: (chunk: string) => {
      try {
        const chunkData = JSON.parse(chunk)

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
      catch (error) {
        console.error('Failed to parse chunk:', error)
      }
    },
    onError: (error: Error) => {
      console.error('Stream error:', error)
      nextTick(() => {
        messages.value.push({
          role: Role.ASSISTANT,
          parts: {
            [ChunkType.TEXT]: 'Sorry, there was an error processing your request. Please try again.',
          },
        })
      })
    },
    onFinish: () => {
      router.reload({
        only: ['chatHistory', 'chat'],
        async: true,
      })
      clearInput()
    },
  },
)

function sendMessage(messageContent: MessageChunks): void {
  const userMessage: Message = {
    role: Role.USER,
    parts: messageContent,
  }

  messages.value.push(userMessage)

  const params: StreamParams = {
    message: messageContent[ChunkType.TEXT] || '',
    model: selectedModel.value.id,
  }

  send(params)
}

async function handleSubmit(): Promise<void> {
  const trimmedInput = input.value.trim()

  if (!trimmedInput || isFetching.value || isStreaming.value || !props.chat.id) {
    return
  }

  clearInput()

  await nextTick(() => {
    const userMessage: Message = {
      role: Role.USER,
      parts: {
        [ChunkType.TEXT]: trimmedInput,
      },
      attachments: [],
    }

    messages.value.push(userMessage)
  })

  const params: StreamParams = {
    message: trimmedInput,
    model: selectedModel.value.id,
  }

  send(params)
}

function stop(): void {
  if (isStreaming.value || isFetching.value) {
    cancel()
  }
}

onMounted(() => {
  if (input.value.trim()) {
    sendMessage({
      [ChunkType.TEXT]: input.value.trim(),
    })
    clearInput()
    return
  }

  const lastMessage = messages.value[messages.value.length - 1]
  if (lastMessage && lastMessage.role === Role.USER) {
    sendMessage(lastMessage.parts)
    clearInput()
  }

  nextTick(() => {
    if (messages.value.length > 0 && chatContainerRef.value) {
      chatContainerRef.value.handleScrollToBottom()
    }
  })
})
</script>

<template>
  <Head :title="pageTitle" />
  <AppLayout :breadcrumbs="breadcrumbs" :chat-history="chatHistory">
    <div class="h-[calc(100vh-4rem)] bg-background">
      <ChatContainer
        ref="chatContainerRef"
        :chat-id="props.chat.id"
        :messages="messages"
        :stream-id="id"
        :is-readonly="false"
        @stop="stop"
        @handle-submit="handleSubmit"
      />
    </div>
  </AppLayout>
</template>
