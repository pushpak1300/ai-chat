<script setup lang="ts">
import type { Model } from '@/constants/models'
import type { BreadcrumbItemType, ChatHistory } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useStorage } from '@vueuse/core'
import { ref } from 'vue'
import ChatContainer from '@/components/chat/ChatContainer.vue'
import { provideVisibility } from '@/composables/useVisibility'
import { AVAILABLE_MODELS, MODEL_KEY } from '@/constants/models'
import AppLayout from '@/layouts/AppLayout.vue'
import { Visibility } from '@/types/enum'

defineProps<{
  chatHistory: ChatHistory
}>()

const breadcrumbs: BreadcrumbItemType[] = [
  {
    title: 'Chat',
    href: route('chats.index'),
  },
]

const input = ref('')
const attachments = ref<Array<string>>([])
const initialVisibilityType = ref<Visibility>(Visibility.PRIVATE)
const selectedModel = useStorage<Model>(MODEL_KEY, AVAILABLE_MODELS.find(m => m.id === 'gemini-2.0-flash-lite') || AVAILABLE_MODELS[0])

provideVisibility(Visibility.PRIVATE, initialVisibilityType)

function setInput(value: string) {
  input.value = value
}

function setAttachments(newAttachments: Array<string>) {
  attachments.value = newAttachments
}

function sendInitialMessage(message: string) {
  router.post(route('chats.store'), {
    message,
    model: selectedModel.value.id,
    visibility: initialVisibilityType.value,
  })
}

function handleSubmit() {
  const trimmedInput = input.value.trim()
  if (trimmedInput) {
    sendInitialMessage(trimmedInput)
  }
}

function append(message: string) {
  sendInitialMessage(message)
}
</script>

<template>
  <Head title="Chat" />
  <AppLayout :breadcrumbs="breadcrumbs" :chat-history="chatHistory">
    <div class="h-[calc(100vh-4rem)] bg-background">
      <ChatContainer
        :input="input" @set-input="setInput"
        @set-attachments="setAttachments" @handle-submit="handleSubmit"
        @append="append"
      />
    </div>
  </AppLayout>
</template>
