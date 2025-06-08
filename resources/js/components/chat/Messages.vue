<script setup lang="ts">
import type { Chunk, Message as MessageType } from '@/types'
import { useJsonStream } from '@laravel/stream-vue'
import { onMounted, toRef, watch } from 'vue'
import Greeting from '@/components/chat/Greeting.vue'
import Message from '@/components/chat/Message.vue'
import ThinkingMessage from '@/components/chat/ThinkingMessage.vue'
import { useMessageScroll } from '@/composables/useMessageScroll'

const props = defineProps<{
  chatId?: string
  streamId?: string
  messages: Array<MessageType>
  isReadonly: boolean
}>()

const emit = defineEmits<{
  updateIsAtBottom: [isAtBottom: boolean]
}>()

const { isFetching, isStreaming } = useJsonStream<Chunk>(`stream/${props.chatId}`, { id: props.streamId })

const {
  containerRef,
  isAtBottom,
  hasSentMessage,
  scrollToBottomInstant,
  scrollToBottomIfNeeded,
} = useMessageScroll(toRef(props, 'messages'), isStreaming)

watch(isAtBottom, (newValue) => {
  emit('updateIsAtBottom', newValue)
})

onMounted(() => {
  scrollToBottomIfNeeded()
})

defineExpose({
  scrollToBottom: scrollToBottomInstant,
})
</script>

<template>
  <div
    ref="containerRef"
    class="flex flex-col h-full overflow-y-auto overflow-x-hidden pt-4 relative"
  >
    <div v-if="messages.length === 0" class="flex-1 flex items-center justify-center">
      <Greeting />
    </div>

    <template v-else>
      <div class="flex flex-col gap-6 min-w-0 px-4 mb-4">
        <Message
          v-for="(message, index) in messages"
          :key="message.id"
          :message="message"
          :chat-id="chatId"
          :is-loading="isStreaming"
          :is-readonly="isReadonly"
          :requires-scroll-padding="hasSentMessage && index === messages.length - 1"
        />

        <ThinkingMessage
          v-if="isFetching"
        />
      </div>
    </template>
  </div>
</template>
