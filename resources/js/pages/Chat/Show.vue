<script setup lang="ts">
import type { BreadcrumbItemType, Chat, ChatHistory, Message, Model } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useStream } from '@laravel/stream-vue'
import { useStorage } from '@vueuse/core'
import { computed, nextTick, onMounted, provide, ref, watch } from 'vue'
import ChatContainer from '@/components/chat/ChatContainer.vue'
import { provideChatInput } from '@/composables/useChatInput'
import { provideVisibility } from '@/composables/useVisibility'
import { MODEL_KEY } from '@/constants/models'
import AppLayout from '@/layouts/AppLayout.vue'
import { Role, Visibility } from '@/types/enum'

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

interface StreamChunk {
  type: 'text' | 'reasoning' | 'error'
  content: string
}

const { input, clearInput } = provideChatInput()
const initialVisibilityType = ref<Visibility>(initialVisibility.value)
const selectedModel = useStorage<Model>(MODEL_KEY, props.availableModels[0])
const messages = ref<Message[]>([...(props.chat?.messages || [])])
const votes = ref<Record<string, unknown>[]>([])
const currentReasoning = ref<string>('')
const isCurrentlyReasoning = ref<boolean>(false)

const { visibility } = provideVisibility(initialVisibility.value, initialVisibilityType)

provide('chatId', props.chat.id)

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

function handleStreamData(data: string): void {
  try {
    const chunk: StreamChunk = JSON.parse(data)

    if (chunk.type === 'reasoning') {
      isCurrentlyReasoning.value = true
      currentReasoning.value += chunk.content
      return
    }

    if (chunk.type === 'text') {
      // If we were reasoning, stop the reasoning state
      if (isCurrentlyReasoning.value) {
        isCurrentlyReasoning.value = false
      }

      const lastMessage = messages.value[messages.value.length - 1]

      if (!lastMessage || lastMessage.role !== Role.ASSISTANT) {
        messages.value.push({
          role: Role.ASSISTANT,
          parts: chunk.content,
        })
      }
      else {
        lastMessage.parts += chunk.content
      }
      return
    }

    if (chunk.type === 'error') {
      console.error('Stream error:', chunk.content)
      isCurrentlyReasoning.value = false

      nextTick(() => {
        messages.value.push({
          role: Role.ASSISTANT,
          parts: 'Sorry, there was an error processing your request. Please try again.',
        })
      })
    }
  } catch (parseError) {
    console.error('Failed to parse stream data:', parseError)
    // Fallback for non-JSON data (backward compatibility)
    const lastMessage = messages.value[messages.value.length - 1]

    if (!lastMessage || lastMessage.role !== Role.ASSISTANT) {
      messages.value.push({
        role: Role.ASSISTANT,
        parts: data,
      })
    }
    else {
      lastMessage.parts += data
    }
  }
}

function handleStreamError(error: Error): void {
  console.error('Stream error:', error)
  isCurrentlyReasoning.value = false

  nextTick(() => {
    messages.value.push({
      role: Role.ASSISTANT,
      parts: 'Sorry, there was an error processing your request. Please try again.',
    })
  })
}

function handleStreamFinish(): void {
  isCurrentlyReasoning.value = false
  router.reload({
    only: ['chatHistory', 'chat'],
    async: true,
  })
  clearInput()
}

const { isFetching, isStreaming, send, cancel, id } = useStream(route('chat.stream', { chat: props.chat.id }), {
  onData: handleStreamData,
  onError: handleStreamError,
  onFinish: handleStreamFinish,
})

function sendInitialMessage(messageContent: string): void {
  const userMessage: Message = {
    role: Role.USER,
    parts: messageContent,
  }

  messages.value.push(userMessage)
  // Reset reasoning state for new message
  currentReasoning.value = ''
  isCurrentlyReasoning.value = false

  const params: StreamParams = {
    message: messageContent,
    model: selectedModel.value.id,
  }

  send(params)
}

async function handleSubmit(): Promise<void> {
  const trimmedInput = input.value.trim()

  if (trimmedInput && !isFetching.value && !isStreaming.value && props.chat.id) {
    clearInput()

    await nextTick(() => {
      const userMessage: Message = {
        role: Role.USER,
        parts: trimmedInput,
        attachments: [],
      }

      messages.value.push(userMessage)
    })

    // Reset reasoning state for new message
    currentReasoning.value = ''
    isCurrentlyReasoning.value = false

    const params: StreamParams = {
      message: trimmedInput,
      model: selectedModel.value.id,
    }

    send(params)
  }
}

function stop(): void {
  if (isStreaming.value || isFetching.value) {
    isCurrentlyReasoning.value = false
    cancel()
  }
}

onMounted(() => {
  if (input.value.trim()) {
    const storedInput = input.value.trim()
    sendInitialMessage(storedInput)
    clearInput()
    return
  }

  const lastMessage = messages.value[messages.value.length - 1]
  if (lastMessage && lastMessage.role === Role.USER) {
    sendInitialMessage(lastMessage.parts)
    clearInput()
  }
})
</script>

<template>
  <Head :title="pageTitle" />
  <AppLayout :breadcrumbs="breadcrumbs" :chat-history="chatHistory">
    <div class="h-[calc(100vh-4rem)] bg-background">
      <ChatContainer
        :chat-id="props.chat.id"
        :messages="messages"
        :stream-id="id"
        :votes="votes"
        :is-readonly="false"
        :reasoning="currentReasoning"
        :is-reasoning="isCurrentlyReasoning"
        @stop="stop"
        @handle-submit="handleSubmit"
      />
    </div>
  </AppLayout>
</template>
