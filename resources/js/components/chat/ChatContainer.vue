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
        @set-messages="$emit('setMessages', $event)"
        @update-is-at-bottom="isAtBottom = $event"
      />
    </div>

    <div class="flex-shrink-0 mx-auto w-full max-w-3xl px-2 sm:px-4 pb-2 sm:pb-4">
      <MultimodalInput
        :input="input"
        :chat-id="chatId"
        :stream-id="streamId"
        :attachments="attachments"
        :messages="messages"
        :is-at-bottom="isAtBottom"
        :selected-visibility-type="initialVisibilityType"
        @set-input="$emit('setInput', $event)"
        @set-attachments="$emit('setAttachments', $event)"
        @set-messages="$emit('setMessages', $event)"
        @append="$emit('append', $event)"
        @stop="$emit('stop')"
        @handle-submit="$emit('handleSubmit')"
        @scroll-to-bottom="handleScrollToBottom"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import Messages from '@/components/chat/Messages.vue'
import MultimodalInput from '@/components/chat/MultimodalInput.vue'
import { type Message } from '@/types/index';
import { Visibility } from '@/types/enum'

withDefaults(defineProps<{
    messages?: Array<Message>
    input: string
    streamId?: string
    attachments?: Array<string>
    votes?: Array<Record<string, any>>
    initialVisibilityType?: Visibility
    isReadonly?: boolean
    chatId?: string
}>(), {
    messages: () => [],
    input: '',
    streamId: '',
    attachments: () => [],
    votes: () => [],
    initialVisibilityType: Visibility.PRIVATE,
    isReadonly: false,
    chatId: '',
})

const isAtBottom = ref(false)
const messagesRef = ref<InstanceType<typeof Messages>>()

const handleScrollToBottom = () => {
    messagesRef.value?.scrollToBottom()
}

defineEmits<{
  setInput: [value: string]
  setMessages: [messages: Array<Message>]
  setAttachments: [attachments: Array<string>]
  append: [message: string]
  stop: []
  handleSubmit: []
}>()
</script>
