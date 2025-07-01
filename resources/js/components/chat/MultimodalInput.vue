<script setup lang="ts">
import type { Message } from '@/types'
import { Icon } from '@iconify/vue'
import { useStream } from '@laravel/stream-vue'
import { AnimatePresence } from 'motion-v'
import { computed, nextTick, ref, watch } from 'vue'
import PreviewAttachment from '@/components/chat/PreviewAttachment.vue'
import SendButton from '@/components/chat/SendButton.vue'
import StopButton from '@/components/chat/StopButton.vue'
import SuggestedActions from '@/components/chat/SuggestedActions.vue'
import Button from '@/components/ui/button/Button.vue'
import Textarea from '@/components/ui/textarea/Textarea.vue'
import { useAuth } from '@/composables/useAuth'
import { useChatInput } from '@/composables/useChatInput'
import { useFileUpload } from '@/composables/useFileUpload'

const props = defineProps<{
  chatId?: string
  streamId?: string
  attachments: Array<string>
  messages: Array<Message>
  isAtBottom: boolean
  isReadonly?: boolean
}>()

const emit = defineEmits<{
  append: [message: string]
  stop: []
  handleSubmit: []
  scrollToBottom: []
}>()

const { isGuest } = useAuth()
const { input } = useChatInput()
const { isFetching, isStreaming } = useStream(`stream/${props.chatId}`, { id: props.streamId })
const { uploadQueue, uploadedFiles, isUploading, uploadFile, removeUploadedFile, clearUploads, getAttachmentFilenames } = useFileUpload()

const textareaRef = ref<HTMLTextAreaElement>()
const fileInputRef = ref<HTMLInputElement>()

const canSendMessage = computed(() => !isFetching.value && !isStreaming.value)
const isDisabled = computed(() => props.isReadonly || isGuest.value)
const showSuggestedActions = computed(() =>
  props.messages.length === 0
  && props.attachments.length === 0
  && uploadQueue.value.length === 0
  && !isDisabled.value,
)
const hasAttachments = computed(() =>
  props.attachments.length > 0 || uploadQueue.value.length > 0 || uploadedFiles.value.length > 0,
)

function adjustHeight() {
  nextTick(() => {
    if (textareaRef.value?.style) {
      textareaRef.value.style.height = `${textareaRef.value.scrollHeight + 2}px`
    }
  })
}

function handleKeyDown(event: KeyboardEvent) {
  if (isDisabled.value) {
    event.preventDefault()
    return
  }

  if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
    event.preventDefault()
    if (canSendMessage.value) {
      submitForm()
    }
  }
}

function submitForm() {
  if (!canSendMessage.value || isDisabled.value)
    return

  emit('handleSubmit')
  nextTick(() => emit('scrollToBottom'))
}

function scrollToBottom() {
  emit('scrollToBottom')
}

function handleFileUpload() {
  fileInputRef.value?.click()
}

async function handleFileSelection(event: Event) {
  const target = event.target as HTMLInputElement
  const files = target.files
  
  if (files) {
    for (const file of Array.from(files)) {
      await uploadFile(file)
    }
  }
  
  // Reset the input
  target.value = ''
}

function handleRemoveAttachment(filename: string) {
  removeUploadedFile(filename)
}

// Expose attachments for parent component
defineExpose({
  getAttachmentFilenames,
  clearUploads,
})

watch(input, adjustHeight, { immediate: true })

watch(isStreaming, (isStreamingNow, wasStreaming) => {
  if (!wasStreaming && isStreamingNow) {
    nextTick(() => emit('scrollToBottom'))
  }
}, { flush: 'post' })
</script>

<template>
  <div class="relative w-full flex flex-col gap-4">
    <AnimatePresence>
      <div v-if="!isAtBottom" class="absolute left-1/2 bottom-28 -translate-x-1/2 z-50">
        <Button
          data-testid="scroll-to-bottom-button"
          class="rounded-full"
          size="icon"
          variant="outline"
          @click="scrollToBottom"
        >
          <Icon icon="lucide:arrow-down" />
        </Button>
      </div>
    </AnimatePresence>

    <SuggestedActions
      v-if="showSuggestedActions"
      @append="(message) => emit('append', message)"
    />

    <div
      v-if="hasAttachments"
      data-testid="attachments-preview"
      class="flex flex-row gap-2 overflow-x-scroll items-end"
    >
      <PreviewAttachment
        v-for="attachment in attachments"
        :key="attachment"
        :attachment="[attachment]"
      />

      <PreviewAttachment
        v-for="file in uploadedFiles"
        :key="file.filename"
        :attachment="[file.filename]"
        @remove="handleRemoveAttachment(file.filename)"
      />

      <PreviewAttachment
        v-for="filename in uploadQueue"
        :key="filename"
        :attachment="[filename]"
        :is-uploading="true"
        :show-remove="false"
      />
    </div>

    <Textarea
      v-if="!isDisabled"
      ref="textareaRef"
      :model-value="input"
      data-testid="multimodal-input"
      :disabled="!canSendMessage"
      class="min-h-[24px] max-h-[calc(75dvh)] overflow-hidden resize-none rounded-2xl !text-base bg-muted pb-10 dark:border-zinc-700 disabled:opacity-50 disabled:cursor-not-allowed"
      rows="2"
      @update:model-value="(value) => input = value"
      @keydown="handleKeyDown"
    />

    <div class="absolute bottom-0 right-0 p-2 w-fit flex flex-row justify-end items-center gap-1">
      <Button
        v-if="!isDisabled"
        variant="ghost"
        size="icon"
        class="h-8 w-8 rounded-full"
        @click="handleFileUpload"
      >
        <Icon icon="lucide:paperclip" class="h-4 w-4" />
      </Button>
      
      <StopButton v-if="isStreaming && !isDisabled" @stop="$emit('stop')" />
      <SendButton
        v-else-if="!isDisabled"
        :upload-queue="uploadQueue"
        :is-processing="!canSendMessage"
        @submit="submitForm"
      />
    </div>

    <!-- Hidden file input -->
    <input
      ref="fileInputRef"
      type="file"
      multiple
      accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif,.mp4,.avi,.mov"
      class="hidden"
      @change="handleFileSelection"
    />
  </div>
</template>
