<script setup lang="ts">
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { type BreadcrumbItemType, type ChatHistory } from '@/types'
import ChatContainer from '@/components/chat/ChatContainer.vue'
import { Visibility } from '@/types/enum'
import { provideVisibility } from '@/composables/useVisibility'
import { useStorage } from '@vueuse/core'

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
const selectedModel = useStorage('selected-model', { id: 'gemini-2.0-flash-lite' })

provideVisibility(Visibility.PRIVATE, initialVisibilityType)

const setInput = (value: string) => {
    input.value = value
}

const setAttachments = (newAttachments: Array<string>) => {
    attachments.value = newAttachments
}

const sendInitialMessage = (message: string) => {
    router.post(route('chats.store'), {
        message,
        model: selectedModel.value.id,
        visibility: initialVisibilityType.value,
    })
}

const handleSubmit = () => {
    const trimmedInput = input.value.trim()
    if (trimmedInput) {
        sendInitialMessage(trimmedInput)
    }
}

const append = (message: string) => {
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
                @append="append" />
        </div>
    </AppLayout>
</template>
