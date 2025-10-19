<script setup lang="ts">
import type { Message as MessageType, StreamEvent } from '@/types'
import { useJsonStream } from '@laravel/stream-vue'
import { useScroll } from '@vueuse/core'
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import Greeting from '@/components/chat/Greeting.vue'
import Message from '@/components/chat/Message.vue'
import ThinkingMessage from '@/components/chat/ThinkingMessage.vue'

const props = defineProps<{
  chatId?: string
  streamId?: string
  messages: Array<MessageType>
  isReadonly: boolean
}>()

const emit = defineEmits<{
  updateIsAtBottom: [isAtBottom: boolean]
}>()

const { isFetching, isStreaming } = useJsonStream<StreamEvent>(
  `stream/${props.chatId}`,
  { id: props.streamId },
)

const containerRef = ref<HTMLElement>()
// @ts-expect-error - containerRef is not typed (for some reason)
const { y, arrivedState } = useScroll(containerRef, { behavior: 'auto' })
const isAtBottom = computed(() => arrivedState.bottom)

function scrollToBottom() {
  if (!containerRef.value)
    return

  const maxScrollTop
    = containerRef.value.scrollHeight - containerRef.value.clientHeight
  y.value = maxScrollTop
}

watch(
  () => props.messages.length,
  (newLength, oldLength) => {
    if (newLength > oldLength && (isAtBottom.value || newLength === 1)) {
      nextTick(scrollToBottom)
    }
  },
)

watch(
  () => props.messages[props.messages.length - 1]?.parts,
  () => {
    if (isAtBottom.value && isStreaming.value) {
      nextTick(scrollToBottom)
    }
  },
  { flush: 'post' },
)

watch(isAtBottom, (newValue) => {
  emit('updateIsAtBottom', newValue)
})

onMounted(() => {
  if (props.messages.length > 0) {
    nextTick(scrollToBottom)
  }
})

defineExpose({
  scrollToBottom,
})
</script>

<template>
  <div
    ref="containerRef"
    class="flex flex-col h-full overflow-y-auto overflow-x-hidden pt-4 relative"
  >
    <div
      v-if="messages.length === 0"
      class="flex-1 flex items-center justify-center"
    >
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
          :requires-scroll-padding="index === messages.length - 1"
        />

        <ThinkingMessage v-if="isFetching" />
      </div>
    </template>
  </div>
</template>
