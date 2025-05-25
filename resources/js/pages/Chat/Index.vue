<template>
  <AppLayout>
    <div class="flex h-full bg-background">
    <div class="flex flex-1 flex-col">

      <!-- Messages Container -->
      <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6">
        <div class="mx-auto max-w-3xl space-y-6">
          <div
            v-for="(message, index) in messages"
            :key="index"
            class="flex"
            :class="message.role === 'user' ? 'justify-end' : 'justify-start'"
          >
            <div
              class="max-w-[70%] rounded-lg px-4 py-2"
              :class="
                message.role === 'user'
                  ? 'bg-primary text-primary-foreground'
                  : 'bg-muted text-muted-foreground'
              "
            >
              <p class="whitespace-pre-wrap">{{ message.content }}</p>
            </div>
          </div>

          <!-- Streaming indicator -->
          <div v-if="isStreaming" class="flex justify-start">
            <div class="max-w-[70%] rounded-lg bg-muted px-4 py-2 text-muted-foreground">
              <div class="flex items-center space-x-2">
                <div class="flex space-x-1">
                  <div class="h-2 w-2 animate-bounce rounded-full bg-gray-400 [animation-delay:-0.3s]"></div>
                  <div class="h-2 w-2 animate-bounce rounded-full bg-gray-400 [animation-delay:-0.15s]"></div>
                  <div class="h-2 w-2 animate-bounce rounded-full bg-gray-400"></div>
                </div>
                <span class="text-sm">AI is typing...</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Input Form -->
      <div class="border-t border-border p-6">
        <form class="mx-auto max-w-3xl">
          <div class="flex space-x-4">
            <textarea
              ref="messageInput"
              v-model="newMessage"
              :disabled="isStreaming"
              placeholder="Type your message..."
              rows="1"
              class="flex-1 resize-none rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
              @input="adjustTextareaHeight"
            />
            <button
              type="submit"
              :disabled="!newMessage.trim() || isStreaming"
              class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
            >
              <svg
                v-if="isStreaming"
                class="h-4 w-4 animate-spin"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
              <span v-else>Send</span>
            </button>
          </div>
        </form>
      </div>
    </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'

interface ChatMessage {
  role: 'user' | 'assistant'
  content: string
}

const messages = ref<ChatMessage[]>([])
const newMessage = ref('')
const isStreaming = ref(false)
const messagesContainer = ref<HTMLElement>()
const messageInput = ref<HTMLTextAreaElement>()

const adjustTextareaHeight = () => {
  if (messageInput.value) {
    messageInput.value.style.height = 'auto'
    messageInput.value.style.height = messageInput.value.scrollHeight + 'px'
  }
}


onMounted(() => {
  // Focus on the input when component mounts
  messageInput.value?.focus()
})
</script>
