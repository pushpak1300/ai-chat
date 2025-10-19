<script setup lang="ts">
import type { Message } from '@/types/index'
import { ref } from 'vue'
import Messages from '@/components/chat/Messages.vue'
import MultimodalInput from '@/components/chat/MultimodalInput.vue'

withDefaults(
  defineProps<{
    messages?: Array<Message>
    streamId?: string
    attachments?: Array<string>
    isReadonly?: boolean
    chatId?: string
  }>(),
  {
    messages: () => [],
    streamId: '',
    attachments: () => [],
    isReadonly: false,
    chatId: '',
  },
)

defineEmits<{
  append: [message: string]
  stop: []
  handleSubmit: []
}>()

const isAtBottom = ref(false)
const messagesRef = ref<any>()

function handleScrollToBottom(): void {
  messagesRef.value?.scrollToBottom()
}

function updateIsAtBottom(value: boolean): void {
  isAtBottom.value = value
}

defineExpose({
  handleScrollToBottom,
})
</script>

<template>
  <div class="flex flex-col h-full bg-background overflow-hidden">
    <div class="flex-1 min-h-0 overflow-hidden">
      <Messages
        ref="messagesRef"
        :chat-id="chatId"
        :stream-id="streamId"
        :messages="messages"
        :is-readonly="isReadonly"
        @update-is-at-bottom="updateIsAtBottom"
      />
    </div>

    <div
      class="flex-shrink-0 mx-auto w-full max-w-3xl px-2 sm:px-4 pb-2 sm:pb-4 mt-2"
    >
      <MultimodalInput
        :chat-id="chatId"
        :stream-id="streamId"
        :attachments="attachments"
        :messages="messages"
        :is-at-bottom="isAtBottom"
        :is-readonly="isReadonly"
        @append="$emit('append', $event)"
        @stop="$emit('stop')"
        @handle-submit="$emit('handleSubmit')"
        @scroll-to-bottom="handleScrollToBottom"
      />
    </div>
  </div>
</template>
