<template>
    <div class="relative w-full flex flex-col gap-4">
        <AnimatePresence>
            <div v-if="!props.isAtBottom" class="absolute left-1/2 bottom-28 -translate-x-1/2 z-50">
                <Button data-testid="scroll-to-bottom-button" class="rounded-full" size="icon" variant="outline"
                    @click="scrollToBottom">
                    <Icon icon="lucide:arrow-down" />
                </Button>
            </div>
        </AnimatePresence>

        <SuggestedActions v-if="messages.length === 0 && attachments.length === 0 && uploadQueue.length === 0"
            @append="(message) => emit('append', message)" />

        <div v-if="attachments.length > 0 || uploadQueue.length > 0" data-testid="attachments-preview"
            class="flex flex-row gap-2 overflow-x-scroll items-end">
            <PreviewAttachment v-for="attachment in attachments" :key="attachment" :attachment="[attachment]" />

            <PreviewAttachment v-for="filename in uploadQueue" :key="filename" :attachment="[filename]"
                :is-uploading="true" />
        </div>

        <Textarea ref="textareaRef" data-testid="multimodal-input" placeholder="Send a message..." :model-value="input"
            :class="[
                'min-h-[24px] max-h-[calc(75dvh)] overflow-hidden resize-none rounded-2xl !text-base bg-muted pb-10 dark:border-zinc-700',
            ]" rows="2" @update:model-value="handleInput" @keydown="handleKeyDown" />

        <div class="absolute bottom-0 p-2 w-fit flex flex-row justify-start">
            <AttachmentsButton :file-input-ref="fileInputRef" :chat-id="chatId" :stream-id="streamId" />
        </div>

        <div class="absolute bottom-0 right-0 p-2 w-fit flex flex-row justify-end">
            <StopButton v-if="isStreaming" @stop="$emit('stop')" @set-messages="$emit('setMessages', $event)" />
            <SendButton v-else :input="input" :upload-queue="uploadQueue" :is-processing="isStreaming"
                @submit="submitForm" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, nextTick, watch } from 'vue'
import { useStream } from '@laravel/stream-vue'
import Textarea from '@/components/ui/textarea/Textarea.vue'
import Button from '@/components/ui/button/Button.vue'
import SuggestedActions from '@/components/chat/SuggestedActions.vue'
import PreviewAttachment from '@/components/chat/PreviewAttachment.vue'
import AttachmentsButton from '@/components/chat/AttachmentsButton.vue'
import StopButton from '@/components/chat/StopButton.vue'
import SendButton from '@/components/chat/SendButton.vue'
import { Icon } from '@iconify/vue'
import { Message } from '@/types'
import { AnimatePresence } from 'motion-v'

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
    setMessages: [messages: Array<Message>]
    append: [message: string]
    stop: []
    handleSubmit: []
    scrollToBottom: []
}>()

const { isFetching, isStreaming } = useStream(`stream/${props.chatId}`, { id: props.streamId })

const textareaRef = ref<HTMLTextAreaElement>()
const fileInputRef = ref<HTMLInputElement>()
const uploadQueue = ref<Array<string>>([])

let adjustHeightTimeout: ReturnType<typeof setTimeout> | null = null

const adjustHeight = () => {
    if (adjustHeightTimeout) {
        clearTimeout(adjustHeightTimeout)
    }

    adjustHeightTimeout = setTimeout(() => {
        if (textareaRef.value?.style) {
            textareaRef.value.style.height = `${textareaRef.value.scrollHeight + 2}px`
        }
    }, 10)
}

const handleInput = (value: string | number) => {
    const stringValue = String(value)
    emit('setInput', stringValue)
    nextTick(() => adjustHeight())
}

const handleKeyDown = (event: KeyboardEvent) => {
    if (
        event.key === 'Enter' &&
        !event.shiftKey &&
        !event.isComposing
    ) {
        event.preventDefault()

        if (isFetching.value || isStreaming.value) {
            event.preventDefault()
            return
        } else {
            submitForm()
        }
    }
}

const submitForm = () => {
    emit('handleSubmit')
    nextTick(() => {
        emit('scrollToBottom')
    })
}

const scrollToBottom = () => {
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
