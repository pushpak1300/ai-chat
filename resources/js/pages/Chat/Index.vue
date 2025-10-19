<script setup lang="ts">
import type {
  BreadcrumbItemType,
  ChatHistory,
  Model,
  SharedData,
} from '@/types'
import { Head, router, usePage } from '@inertiajs/vue3'
import { useStorage } from '@vueuse/core'
import { ref } from 'vue'
import ChatContainer from '@/components/chat/ChatContainer.vue'
import { provideChatInput } from '@/composables/useChatInput'
import { provideVisibility } from '@/composables/useVisibility'
import { MODEL_KEY } from '@/constants/models'
import AppLayout from '@/layouts/AppLayout.vue'
import { Visibility } from '@/types/enum'

const props = defineProps<{
  chatHistory?: ChatHistory
  availableModels: Model[]
}>()

const page = usePage<SharedData>()
const isGuest = !page.props.auth.user

const breadcrumbs: BreadcrumbItemType[] = [
  {
    title: 'Chat',
    href: route('chats.index'),
  },
]

interface ChatCreateParams {
  message: string
  model: string
  visibility: Visibility
}

const { input } = provideChatInput()
const initialVisibilityType = ref<Visibility>(Visibility.PRIVATE)
const selectedModel = useStorage<Model>(MODEL_KEY, props.availableModels[0])

provideVisibility(Visibility.PRIVATE, initialVisibilityType)

function sendInitialMessage(userMessage: string): void {
  if (isGuest) {
    return
  }

  const params: ChatCreateParams = {
    message: userMessage,
    model: selectedModel.value.id,
    visibility: initialVisibilityType.value,
  }

  router.post(route('chats.store'), params as Record<string, any>)
}

function handleSubmit(): void {
  if (isGuest) {
    return
  }

  const trimmedInput = input.value.trim()
  if (trimmedInput) {
    sendInitialMessage(trimmedInput)
  }
}

function append(message: string): void {
  if (isGuest) {
    return
  }

  input.value = message
  sendInitialMessage(message)
}
</script>

<template>
  <Head title="Chat" />
  <AppLayout :breadcrumbs="breadcrumbs" :chat-history="chatHistory">
    <div class="h-[calc(100vh-4rem)] bg-background">
      <ChatContainer
        :is-readonly="isGuest"
        @handle-submit="handleSubmit"
        @append="append"
      />
    </div>
  </AppLayout>
</template>
