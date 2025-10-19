<script setup lang="ts">
import type {
  BreadcrumbItemType,
  Chat,
  ChatHistory,
  MessageParts,
  Model,
} from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useStorage } from '@vueuse/core'
import { computed, nextTick, onMounted, provide, ref, watch } from 'vue'
import ChatContainer from '@/components/chat/ChatContainer.vue'
import { provideChatInput } from '@/composables/useChatInput'
import { useChatMessages } from '@/composables/useChatMessages'
import { useMessageStream } from '@/composables/useMessageStream'
import { provideVisibility } from '@/composables/useVisibility'
import { MODEL_KEY } from '@/constants/models'
import AppLayout from '@/layouts/AppLayout.vue'
import { ContentType, Visibility } from '@/types/enum'

const props = defineProps<{
  chatHistory?: ChatHistory
  chat: Chat
  availableModels: Model[]
}>()

const pageTitle = computed<string>(() => props.chat?.title || 'Chat')
const initialVisibility = computed<Visibility>(
  () => props.chat?.visibility || Visibility.PRIVATE,
)

const breadcrumbs: BreadcrumbItemType[] = [
  {
    title: 'Chat',
    href: route('chats.index'),
  },
]

const { input, clearInput } = provideChatInput()
const initialVisibilityType = ref<Visibility>(initialVisibility.value)
const selectedModel = useStorage<Model>(MODEL_KEY, props.availableModels[0])
const chatContainerRef = ref<InstanceType<typeof ChatContainer>>()

const { visibility } = provideVisibility(
  initialVisibility.value,
  initialVisibilityType,
)
const {
  messages,
  addTextMessage,
  scrollToBottom,
  getLastMessage,
  isLastMessageFromUser,
} = useChatMessages(props.chat, chatContainerRef)

const { isFetching, isStreaming, send, cancel, id } = useMessageStream(
  props.chat.id,
  messages,
  clearInput,
)

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

watch(
  visibility,
  (newVisibility, oldVisibility) => {
    if (oldVisibility !== undefined && newVisibility !== oldVisibility) {
      updateChatVisibility(newVisibility)
    }
  },
  { immediate: false },
)

function sendMessage(messageContent: MessageParts): void {
  addTextMessage(messageContent[ContentType.TEXT] || '')

  send({
    message: messageContent[ContentType.TEXT] || '',
    model: selectedModel.value.id,
  })
}

async function handleSubmit(): Promise<void> {
  const trimmedInput = input.value.trim()

  if (
    !trimmedInput
    || isFetching.value
    || isStreaming.value
    || !props.chat.id
  ) {
    return
  }

  clearInput()

  await nextTick(() => {
    addTextMessage(trimmedInput)
  })

  send({
    message: trimmedInput,
    model: selectedModel.value.id,
  })
}

function stop(): void {
  if (isStreaming.value || isFetching.value) {
    cancel()
  }
}

onMounted(() => {
  if (input.value.trim()) {
    sendMessage({ [ContentType.TEXT]: input.value.trim() })
    clearInput()
    return
  }

  const lastMessage = getLastMessage()
  if (lastMessage && isLastMessageFromUser()) {
    sendMessage(lastMessage.parts)
    clearInput()
  }

  nextTick(() => {
    if (messages.value.length > 0) {
      scrollToBottom()
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
