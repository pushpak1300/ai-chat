<script setup lang="ts">
import type { Model } from '@/constants/models'
import type { BreadcrumbItemType, ChatHistory } from '@/types'
import { Head, router, usePage } from '@inertiajs/vue3'
import { useStorage } from '@vueuse/core'
import { computed, ref } from 'vue'
import ChatContainer from '@/components/chat/ChatContainer.vue'
import { provideVisibility } from '@/composables/useVisibility'
import { MODEL_KEY } from '@/constants/models'
import AppLayout from '@/layouts/AppLayout.vue'
import { Visibility } from '@/types/enum'

defineProps<{
  chatHistory: ChatHistory
}>()

const page = usePage()
const availableModels = computed(() => page.props.availableModels as Model[])

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

const input = ref<string>('')
const initialVisibilityType = ref<Visibility>(Visibility.PRIVATE)
const selectedModel = useStorage<Model>(
  MODEL_KEY,
  availableModels.value.find((m): boolean => m.id === 'gemini-2.0-flash-lite') ?? availableModels.value[0],
)

provideVisibility(Visibility.PRIVATE, initialVisibilityType)

function setInput(value: string): void {
  input.value = value
}

function sendInitialMessage(userMessage: string): void {
  const params: ChatCreateParams = {
    message: userMessage,
    model: selectedModel.value.id,
    visibility: initialVisibilityType.value,
  }

  router.post(route('chats.store'), params)
}

function handleSubmit(): void {
  const trimmedInput = input.value.trim()
  if (trimmedInput) {
    sendInitialMessage(trimmedInput)
  }
}

function append(message: string): void {
  sendInitialMessage(message)
}
</script>

<template>
  <Head title="Chat" />
  <AppLayout :breadcrumbs="breadcrumbs" :chat-history="chatHistory">
    <div class="h-[calc(100vh-4rem)] bg-background">
      <ChatContainer
        :input="input"
        @set-input="setInput"
        @handle-submit="handleSubmit"
        @append="append"
      />
    </div>
  </AppLayout>
</template>
