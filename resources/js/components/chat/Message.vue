<script setup lang="ts">
import type { Message } from '@/types'
import { Icon } from '@iconify/vue'
import { AnimatePresence, motion } from 'motion-v'
import { ref } from 'vue'
import { useMessageFormatting } from '@/composables/useMessageFormatting'
import { ContentType, Role } from '@/types/enum'
import MarkdownRenderer from './MarkdownRenderer.vue'
import MessageActions from './MessageActions.vue'
import ReasoningDisplay from './ReasoningDisplay.vue'

const props = defineProps<{
  message: Message
  isLoading: boolean
  requiresScrollPadding: boolean
  isReadonly?: boolean
  chatId?: string
}>()

const mode = ref<'view' | 'edit'>('view')
const { messageParts } = useMessageFormatting(props.message)

const isUserMessage = props.message.role === Role.USER
const isAssistantMessage = props.message.role === Role.ASSISTANT
</script>

<template>
  <AnimatePresence>
    <motion.div
      :key="message.id"
      :data-testid="`message-${message.role}`"
      class="w-full mx-auto max-w-3xl px-4 group/message"
      :initial="{ y: 5, opacity: 0 }"
      :animate="{ y: 0, opacity: 1 }"
      :data-role="message.role"
    >
      <div
        class="flex flex-col md:flex-row gap-2 md:gap-4 w-full group-data-[role=user]/message:ml-auto group-data-[role=user]/message:max-w-2xl"
        :class="[
          {
            'w-full': mode === 'edit',
            'group-data-[role=user]/message:w-fit': mode !== 'edit',
          },
        ]"
      >
        <div
          v-if="isAssistantMessage"
          class="size-8 flex items-center rounded-full justify-center ring-1 shrink-0 ring-border bg-background"
        >
          <div class="translate-y-px">
            <Icon icon="lucide:sparkles" class="size-4" />
          </div>
        </div>

        <div
          class="flex flex-col gap-2 w-full"
          :class="[
            {
              'min-h-96':
                isAssistantMessage && requiresScrollPadding,
            },
          ]"
        >
          <template
            v-for="(part, partIndex) in messageParts"
            :key="`${message.id}-${partIndex}`"
          >
            <ReasoningDisplay
              v-if="part[ContentType.THINKING]"
              :content="part[ContentType.THINKING]"
              :is-loading="isLoading"
            />

            <div
              v-else-if="part[ContentType.TEXT]"
              class="flex flex-row gap-2 items-start"
            >
              <div
                v-if="mode === 'view'"
                data-testid="message-content"
                class="flex flex-col gap-4 min-w-0 overflow-hidden"
                :class="[
                  {
                    'bg-primary text-primary-foreground px-3 py-2 rounded-xl':
                      isUserMessage,
                  },
                ]"
              >
                <div
                  v-if="part[ContentType.TEXT]"
                  class="w-full"
                >
                  <MarkdownRenderer
                    v-if="isAssistantMessage"
                    :content="part[ContentType.TEXT]"
                  />
                  <div
                    v-else
                    class="whitespace-pre-wrap"
                    v-text="part[ContentType.TEXT]"
                  />
                </div>

                <div
                  v-else
                  class="w-full text-muted-foreground italic"
                >
                  No content available
                </div>
              </div>
            </div>
          </template>

          <MessageActions
            v-if="!isLoading && !isReadonly"
            :key="`action-${message.id}`"
            :message="message"
            :chat-id="chatId"
            :is-loading="isLoading"
          />
        </div>
      </div>
    </motion.div>
  </AnimatePresence>
</template>
