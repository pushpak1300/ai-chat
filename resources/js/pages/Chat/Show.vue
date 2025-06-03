<script setup lang="ts">
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { useStream } from '@laravel/stream-vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { BreadcrumbItemType, Chat, ChatHistory, Message } from '@/types'
import ChatContainer from '@/components/chat/ChatContainer.vue'
import { Role, Visibility } from '@/types/enum'
import { provideVisibility } from '@/composables/useVisibility'
import { useStorage } from '@vueuse/core'

const props = defineProps<{
    chatHistory: ChatHistory
    chat: Chat,
}>()

const breadcrumbs: BreadcrumbItemType[] = [
  {
    title: 'Chat',
    href: route('chats.index'),
  },
]

const initialVisibilityType = ref<Visibility>(props.chat?.visibility || Visibility.PRIVATE)
const selectedModel = useStorage('selected-model', { id: 'gemini-2.0-flash-lite' })
const messages = ref<Array<Message>>([...props.chat?.messages || []])
const input = ref('')
const attachments = ref<Array<string>>([])
const votes = ref<Array<Record<string, any>>>([])

const { visibility } = provideVisibility(props.chat?.visibility || Visibility.PRIVATE, initialVisibilityType)

const updateChatVisibility = (newVisibility: Visibility) => {
  router.patch(route('chats.update', { chat: props.chat.id }), {
    visibility: newVisibility
  }, {
    preserveState: true,
    preserveScroll: true,
    only: []
  })
}

watch(visibility, (newVisibility, oldVisibility) => {
  if (oldVisibility !== undefined && newVisibility !== oldVisibility) {
    updateChatVisibility(newVisibility)
  }
}, { immediate: false })

const { isFetching, isStreaming, send, cancel, id } = useStream(`stream/${props.chat.id}`, {
  onData: (chunk: string) => {
    const lastMessage = messages.value[messages.value.length - 1]

    if (!lastMessage || lastMessage.role !== Role.ASSISTANT) {
      messages.value.push({
        role: Role.ASSISTANT,
        parts: chunk,
      })
    } else {
      lastMessage.parts += chunk
    }
  },
  onError: (error: Error) => {
    console.error('Stream error:', error)
    nextTick(() => {
      messages.value.push({
        role: Role.ASSISTANT,
        parts: 'Sorry, there was an error processing your request. Please try again.',
      })
    })
  },
  onFinish: () => {
    router.reload();
  }
})

const sendInitialMessage = (messageContent: string) => {
  messages.value.push({
    role: Role.USER,
    parts: messageContent,
  })

  messages.value.push({
    role: Role.ASSISTANT,
    parts: '',
  })

  send({
    message: messageContent,
    model: selectedModel.value.id,
    visibility: initialVisibilityType.value,
  })
}

onMounted(() => {
  if (messages.value.length === 0 && props.chat.title) {
    sendInitialMessage(props.chat.title)
  } else if (messages.value.length > 0) {
    const lastMessage = messages.value[messages.value.length - 1]

    if (lastMessage.role === Role.USER) {
      messages.value.push({
        role: Role.ASSISTANT,
        parts: '',
      })

      send({
        message: lastMessage.parts,
        model: selectedModel.value.id,
        visibility: initialVisibilityType.value,
        intialMessage: false
      })
    }
  }
})

const setInput = (value: string) => {
  input.value = value
}

const setMessage = async (message: Message) => {
router.patch(route('chats.update', { chat: props.chat.id }), {
    message_id: message.id,
    message: message.parts,
  }, {
    async: true,
    onSuccess: () => {
        messages.value.splice(messages.value.length - 1, 1, message)
    }
  })
}

const setAttachments = (newAttachments: Array<string>) => {
  attachments.value = newAttachments
}

const handleSubmit = async () => {
  if (input.value.trim() && !isFetching.value && !isStreaming.value && props.chat.id) {
    const userMessage = input.value.trim()
    input.value = ''

    await nextTick(() => {
      messages.value.push({
        role: Role.USER,
        parts: userMessage,
        attachments: [],
      })
    })

    send({
      message: userMessage,
      model: selectedModel.value.id,
      visibility: initialVisibilityType.value,
    })
  }
}



const stop = () => {
  cancel()
}

const pageTitle = computed(() => {
  return props.chat?.title || 'Chat'
})

</script>

<template>
  <Head :title="pageTitle" />
  <AppLayout :breadcrumbs="breadcrumbs" :chat-history="chatHistory">
    <div class="h-[calc(100vh-4rem)] bg-background">
      <ChatContainer
        :chat-id="props.chat.id"
        :messages="messages"
        :input="input"
        :stream-id="id"
        :attachments="attachments"
        :votes="votes"
        :is-readonly="false"
        @set-input="setInput"
        @set-message="setMessage"
        @set-attachments="setAttachments"
        @stop="stop"
        @handle-submit="handleSubmit"
      />
    </div>
  </AppLayout>
</template>
