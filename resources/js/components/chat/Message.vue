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
        :class="[
          'flex flex-col md:flex-row gap-2 md:gap-4 w-full group-data-[role=user]/message:ml-auto group-data-[role=user]/message:max-w-2xl',
          {
            'w-full': mode === 'edit',
            'group-data-[role=user]/message:w-fit': mode !== 'edit'
          }
        ]"
      >
        <div
          v-if="message.role === Role.ASSISTANT"
          class="size-8 flex items-center rounded-full justify-center ring-1 shrink-0 ring-border bg-background"
        >
          <div class="translate-y-px">
            <Icon icon="lucide:sparkles" class="size-3.5" />
          </div>
        </div>

        <div
          :class="[
            'flex flex-col gap-2 md:gap-4 w-full',
            {
              'min-h-96': message.role === 'assistant' && requiresScrollPadding
            }
          ]"
        >
          <div
            v-if="message.attachments && message.attachments.length > 0"
            data-testid="message-attachments"
            class="flex flex-row justify-end gap-2"
          >
          </div>
          <div class="flex flex-row gap-2 items-start">
            <Tooltip v-if="message.role === 'user' && !isLoading">
              <TooltipTrigger as-child>
                <Button
                  data-testid="message-edit-button"
                  variant="ghost"
                  class="px-2 h-fit rounded-full text-muted-foreground opacity-0 group-hover/message:opacity-100"
                  @click="mode = 'edit'"
                >
                  <Icon icon="lucide:pencil" class="size-4" />
                </Button>
              </TooltipTrigger>
              <TooltipContent>Edit message</TooltipContent>
            </Tooltip>

            <div
              v-if="mode === 'view'"
              data-testid="message-content"
              :class="[
                'flex flex-col gap-4 min-w-0 overflow-hidden',
                {
                  'bg-primary text-primary-foreground px-3 py-2 rounded-xl': message.role === 'user'
                }
              ]"
            >
              <div v-if="message.parts" class="w-full">
                <MarkdownRenderer :content="message.parts" />
              </div>

              <div v-else class="w-full text-muted-foreground italic">
                No content available
              </div>
            </div>

            <MessageEditor
              v-else-if="mode === 'edit'"
              :key="message.id"
              :message="message"
              @set-mode="mode = $event"
              @set-message="emit('setMessage', $event)"
            />
          </div>

          <MessageActions
            v-if="!isLoading"
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

<script setup lang="ts">
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import MessageActions from './MessageActions.vue'
import MessageEditor from './MessageEditor.vue'
import MarkdownRenderer from './MarkdownRenderer.vue'
import { Icon } from '@iconify/vue'
import { type Message } from '@/types'
import { Role } from '@/types/enum'
import { AnimatePresence, motion } from 'motion-v'

defineProps<{
    message: Message
    isLoading: boolean
    requiresScrollPadding: boolean
    isReadonly?: boolean
    chatId?: string
}>()

const emit = defineEmits<{
  setMessage: [message: Message]
}>()

const mode = ref<'view' | 'edit'>('view')
</script>
