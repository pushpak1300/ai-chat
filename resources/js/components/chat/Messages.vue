<script setup lang="ts">
import type { Message as MessageType } from '@/types'
import { useStream } from '@laravel/stream-vue'
import { nextTick, onMounted, ref, watch } from 'vue'
import Greeting from '@/components/chat/Greeting.vue'
import Message from '@/components/chat/Message.vue'
import ThinkingMessage from '@/components/chat/ThinkingMessage.vue'
import { useScrollToBottom } from '@/composables/useScrollToBottom'
import { Role } from '@/types/enum'

const props = defineProps<{
  chatId?: string
  streamId?: string
  votes?: Array<Record<string, any>>
  messages: Array<MessageType>
  isReadonly: boolean
  reasoning?: string
  isReasoning?: boolean
}>()

const emit = defineEmits<{
  updateIsAtBottom: [isAtBottom: boolean]
}>()

const { isFetching, isStreaming } = useStream(`stream/${props.chatId}`, { id: props.streamId })

const {
  containerRef,
  isAtBottom,
  scrollToBottomInstant,
} = useScrollToBottom()

const hasSentMessage = ref(false)
let scrollTimeout: ReturnType<typeof setTimeout> | null = null

watch(() => props.messages.length, (newLength, oldLength) => {
  if (newLength > oldLength) {
    hasSentMessage.value = true
    if (isAtBottom.value || newLength === 1) {
      nextTick(() => {
        scrollToBottomInstant()
      })
    }
  }
})

watch(() => props.messages[props.messages.length - 1]?.parts, () => {
  if (isAtBottom.value && isStreaming.value) {
    if (scrollTimeout) {
      clearTimeout(scrollTimeout)
    }
    scrollTimeout = setTimeout(() => {
      scrollToBottomInstant()
    }, 100)
  }
}, { flush: 'post' })

watch(isAtBottom, (newValue) => {
  emit('updateIsAtBottom', newValue)
})

onMounted(() => {
  nextTick(() => {
    if (props.messages.length > 0) {
      scrollToBottomInstant()
    }
  })
})

defineExpose({
  scrollToBottom: scrollToBottomInstant,
})

function shouldShowReasoning(message: MessageType, index: number): boolean {
  return message.role === Role.ASSISTANT
    && index === props.messages.length - 1
    && (props.isReasoning || !!props.reasoning)
}
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
      <div class="flex flex-col gap-6 min-w-0 px-4">
        <Message
          v-for="(message, index) in messages"
          :key="message.id"
          :message="message"
          :chat-id="chatId"
          :is-loading="isStreaming && messages.length - 1 === index"
          :is-readonly="isReadonly"
          :requires-scroll-padding="hasSentMessage && index === messages.length - 1"
          :reasoning="shouldShowReasoning(message, index) ? reasoning : undefined"
          :is-reasoning="shouldShowReasoning(message, index) ? isReasoning : false"
        />

        <ThinkingMessage
          v-if="isFetching"
        />
      </div>
    </template>
  </div>
</template>
