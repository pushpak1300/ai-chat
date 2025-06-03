<script setup lang="ts">
import type { Model } from '@/constants/models'
import type { BreadcrumbItemType, Chat, ChatHistory, Message } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useStream } from '@laravel/stream-vue'
import { useStorage } from '@vueuse/core'
import { computed, nextTick, onMounted, provide, ref, watch } from 'vue'
import ChatContainer from '@/components/chat/ChatContainer.vue'
import { provideVisibility } from '@/composables/useVisibility'
import { AVAILABLE_MODELS, MODEL_KEY } from '@/constants/models'
import AppLayout from '@/layouts/AppLayout.vue'
import { Role, Visibility } from '@/types/enum'

const props = defineProps<{
  chatHistory: ChatHistory
  chat: Chat
}>()

const breadcrumbs: BreadcrumbItemType[] = [
  {
    title: 'Chat',
    href: route('chats.index'),
  },
]

const initialVisibilityType = ref<Visibility>(props.chat?.visibility || Visibility.PRIVATE)
const selectedModel = useStorage<Model>(MODEL_KEY, AVAILABLE_MODELS[0])
const messages = ref<Array<Message>>([...props.chat?.messages || []])
const input = ref('')
const votes = ref<Array<Record<string, any>>>([])

const { visibility } = provideVisibility(props.chat?.visibility || Visibility.PRIVATE, initialVisibilityType)

provide('chatId', props.chat.id)

function updateChatVisibility(newVisibility: Visibility) {
  router.patch(route('chats.update', { chat: props.chat.id }), {
    visibility: newVisibility,
  }, {
    preserveState: true,
    preserveScroll: true,
    only: [],
  })
}

watch(visibility, (newVisibility, oldVisibility) => {
  if (oldVisibility !== undefined && newVisibility !== oldVisibility) {
    updateChatVisibility(newVisibility)
  }
}, { immediate: false })

const { isFetching, isStreaming, send, cancel, id } = useStream(`stream/${props.chat.id}`, {
  onData: (chunk: string) => {
    const lastMessage = messages.value[messages.value.length - 1]

    if (!lastMessage || lastMessage.role !== Role.ASSISTANT) {
      messages.value.push({
        role: Role.ASSISTANT,
        parts: chunk,
      })
    }
    else {
      lastMessage.parts += chunk
    }
  },
  onError: (error: Error) => {
    console.error('Stream error:', error)
    nextTick(() => {
      messages.value.push({
        role: Role.ASSISTANT,
        parts: 'Sorry, there was an error processing your request. Please try again.',
      })
    })
  },
  onFinish: () => {
    router.reload()
  },
})

function sendInitialMessage(messageContent: string) {
  messages.value.push({
    role: Role.USER,
    parts: messageContent,
  })

  messages.value.push({
    role: Role.ASSISTANT,
    parts: '',
  })

  send({
    message: messageContent,
    model: selectedModel.value.id,
    visibility: initialVisibilityType.value,
  })
}

onMounted(() => {
  if (messages.value.length === 0 && props.chat.title) {
    sendInitialMessage(props.chat.title)
  }
})

function setInput(value: string) {
  input.value = value
}

async function handleSubmit() {
  if (input.value.trim() && !isFetching.value && !isStreaming.value && props.chat.id) {
    const userMessage = input.value.trim()
    input.value = ''

    await nextTick(() => {
      messages.value.push({
        role: Role.USER,
        parts: userMessage,
        attachments: [],
      })
    })

    send({
      message: userMessage,
      model: selectedModel.value.id,
      visibility: initialVisibilityType.value,
    })
  }
}

function stop() {
  cancel()
}

const pageTitle = computed(() => {
  return props.chat?.title || 'Chat'
})
</script>

<template>
  <Head :title="pageTitle" />
  <AppLayout :breadcrumbs="breadcrumbs" :chat-history="chatHistory">
    <div class="h-[calc(100vh-4rem)] bg-background">
      <ChatContainer
        :chat-id="props.chat.id"
        :messages="messages"
        :input="input"
        :stream-id="id"
        :votes="votes"
        :is-readonly="false"
        @set-input="setInput"
        @stop="stop"
        @handle-submit="handleSubmit"
      />
    </div>
  </AppLayout>
</template>
