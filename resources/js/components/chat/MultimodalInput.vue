<script setup lang="ts">
import type { Message } from '@/types'
import { Icon } from '@iconify/vue'
import { useStream } from '@laravel/stream-vue'
import { AnimatePresence } from 'motion-v'
import { nextTick, ref, watch } from 'vue'
import PreviewAttachment from '@/components/chat/PreviewAttachment.vue'
import SendButton from '@/components/chat/SendButton.vue'
import StopButton from '@/components/chat/StopButton.vue'
import SuggestedActions from '@/components/chat/SuggestedActions.vue'
import Button from '@/components/ui/button/Button.vue'
import Textarea from '@/components/ui/textarea/Textarea.vue'

const props = defineProps<{
  input: string
  chatId?: string
  streamId?: string
  attachments: Array<string>
  messages: Array<Message>
  isAtBottom: boolean
}>()

const emit = defineEmits<{
  setInput: [value: string]
  setAttachments: [attachments: Array<string>]
  append: [message: string]
  stop: []
  handleSubmit: []
  scrollToBottom: []
}>()

const { isFetching, isStreaming } = useStream(`stream/${props.chatId}`, { id: props.streamId })

const textareaRef = ref<HTMLTextAreaElement>()
const uploadQueue = ref<Array<string>>([])

let adjustHeightTimeout: ReturnType<typeof setTimeout> | null = null

function adjustHeight() {
  if (adjustHeightTimeout) {
    clearTimeout(adjustHeightTimeout)
  }

  adjustHeightTimeout = setTimeout(() => {
    if (textareaRef.value?.style) {
      textareaRef.value.style.height = `${textareaRef.value.scrollHeight + 2}px`
    }
  }, 10)
}

function handleInput(value: string | number) {
  const stringValue = String(value)
  emit('setInput', stringValue)
  nextTick(() => adjustHeight())
}

function handleKeyDown(event: KeyboardEvent) {
  if (
    event.key === 'Enter'
    && !event.shiftKey
    && !event.isComposing
  ) {
    event.preventDefault()

    if (isFetching.value || isStreaming.value) {
      event.preventDefault()
    }
    else {
      submitForm()
    }
  }
}

function submitForm() {
  emit('handleSubmit')
  nextTick(() => {
    emit('scrollToBottom')
  })
}

function scrollToBottom() {
  emit('scrollToBottom')
}

watch(() => props.input, () => {
  nextTick(() => adjustHeight())
}, { immediate: true })

watch(() => isStreaming.value, (newValue, oldValue) => {
  if (!oldValue && newValue) {
    nextTick(() => {
      emit('scrollToBottom')
    })
  }
}, { flush: 'post' })
</script>

<template>
  <div class="relative w-full flex flex-col gap-4">
    <AnimatePresence>
      <div v-if="!props.isAtBottom" class="absolute left-1/2 bottom-28 -translate-x-1/2 z-50">
        <Button
          data-testid="scroll-to-bottom-button" class="rounded-full" size="icon" variant="outline"
          @click="scrollToBottom"
        >
          <Icon icon="lucide:arrow-down" />
        </Button>
      </div>
    </AnimatePresence>

    <SuggestedActions
      v-if="messages.length === 0 && attachments.length === 0 && uploadQueue.length === 0"
      @append="(message) => emit('append', message)"
    />

    <div
      v-if="attachments.length > 0 || uploadQueue.length > 0" data-testid="attachments-preview"
      class="flex flex-row gap-2 overflow-x-scroll items-end"
    >
      <PreviewAttachment v-for="attachment in attachments" :key="attachment" :attachment="[attachment]" />

      <PreviewAttachment
        v-for="filename in uploadQueue" :key="filename" :attachment="[filename]"
        :is-uploading="true"
      />
    </div>

    <Textarea
      ref="textareaRef" data-testid="multimodal-input" placeholder="Send a message..." :model-value="input"
      class="min-h-[24px] max-h-[calc(75dvh)] overflow-hidden resize-none rounded-2xl !text-base bg-muted pb-10 dark:border-zinc-700" rows="2" @update:model-value="handleInput" @keydown="handleKeyDown"
    />

    <div class="absolute bottom-0 right-0 p-2 w-fit flex flex-row justify-end">
      <StopButton v-if="isStreaming" @stop="$emit('stop')" />
      <SendButton
        v-else :input="input" :upload-queue="uploadQueue" :is-processing="isStreaming"
        @submit="submitForm"
      />
    </div>
  </div>
</template>
