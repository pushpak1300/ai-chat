<script setup lang="ts">
import type { Message } from '@/types/index'
import { ref } from 'vue'
import Messages from '@/components/chat/Messages.vue'
import MultimodalInput from '@/components/chat/MultimodalInput.vue'

withDefaults(defineProps<{
  messages?: Array<Message>
  streamId?: string
  attachments?: Array<string>
  votes?: Array<Record<string, any>>
  isReadonly?: boolean
  chatId?: string
  reasoning?: string
  isReasoning?: boolean
}>(), {
  messages: () => [],
  streamId: '',
  attachments: () => [],
  votes: () => [],
  isReadonly: false,
  chatId: '',
  reasoning: '',
  isReasoning: false,
})

defineEmits<{
  append: [message: string]
  stop: []
  handleSubmit: []
}>()

const isAtBottom = ref(false)
const messagesRef = ref<InstanceType<typeof Messages>>()

function handleScrollToBottom() {
  messagesRef.value?.scrollToBottom()
}
</script>

<template>
  <div class="flex flex-col h-full bg-background overflow-hidden">
    <div class="flex-1 min-h-0 overflow-hidden">
      <Messages
        ref="messagesRef"
        :chat-id="chatId"
        :stream-id="streamId"
        :votes="votes"
        :messages="messages"
        :is-readonly="isReadonly"
        :reasoning="reasoning"
        :is-reasoning="isReasoning"
        @update-is-at-bottom="isAtBottom = $event"
      />
    </div>

    <div class="flex-shrink-0 mx-auto w-full max-w-3xl px-2 sm:px-4 pb-2 sm:pb-4">
      <MultimodalInput
        :chat-id="chatId"
        :stream-id="streamId"
        :attachments="attachments"
        :messages="messages"
        :is-at-bottom="isAtBottom"
        @append="$emit('append', $event)"
        @stop="$emit('stop')"
        @handle-submit="$emit('handleSubmit')"
        @scroll-to-bottom="handleScrollToBottom"
      />
    </div>
  </div>
</template>
