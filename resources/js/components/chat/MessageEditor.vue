<template>
  <div class="flex flex-col gap-2 w-full">
    <Textarea
      ref="textareaRef"
      v-model="editedContent"
      class="min-h-[100px] resize-none"
      placeholder="Edit your message..."
      @keydown="handleKeyDown"
    />

    <div class="flex gap-2 justify-end">
      <Button
        variant="outline"
        size="sm"
        @click="handleCancel"
      >
        Cancel
      </Button>
      <Button
        size="sm"
        :disabled="!editedContent.trim()"
        @click="handleSave"
      >
        Save
      </Button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Button } from '@/components/ui/button'
import { Textarea } from '@/components/ui/textarea'
import { type Message } from '@/types'

interface Props {
  message: Message
}

const props = defineProps<Props>()

const emit = defineEmits<{
  setMode: [mode: 'view' | 'edit']
  setMessages: [messages: Array<Message>]
  reload: []
}>()

const textareaRef = ref<HTMLTextAreaElement>()
const editedContent = ref(props.message.parts || '')

const handleKeyDown = (event: KeyboardEvent) => {
  if (event.key === 'Escape') {
    handleCancel()
  } else if (event.key === 'Enter' && (event.metaKey || event.ctrlKey)) {
    handleSave()
  }
}

const handleCancel = () => {
  editedContent.value = props.message.parts || ''
  emit('setMode', 'view')
}

const handleSave = () => {
  if (editedContent.value.trim()) {
    const updatedMessage = {
      ...props.message,
      parts: editedContent.value.trim()
    }

    emit('setMode', 'view')
    console.log('Message updated:', updatedMessage)
  }
}

onMounted(() => {
  textareaRef.value?.focus()
  textareaRef.value?.select()
})
</script>
