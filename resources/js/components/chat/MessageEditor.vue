<template>
  <div class="flex flex-col gap-2 w-full">
    <Textarea
      ref="textareaRef"
      v-model="message"
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
        :disabled="!message.trim()"
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

const props = defineProps<{
    message: Message
}>()

const emit = defineEmits<{
  setMode: [mode: 'view' | 'edit']
  setMessage: [message: Message]
}>()

const textareaRef = ref<InstanceType<typeof Textarea>>()

const message = ref(props.message.parts || '')

const handleKeyDown = (event: KeyboardEvent) => {
  if (event.key === 'Escape') {
    handleCancel()
  } else if (event.key === 'Enter' && (event.metaKey || event.ctrlKey)) {
    handleSave()
  }
}

const handleCancel = () => {
  emit('setMode', 'view')
}

const handleSave = () => {
  if (message.value.trim()) {
    emit('setMessage', {
      ...props.message,
      parts: message.value.trim()
    })
    emit('setMode', 'view')
  }
}

onMounted(() => {
  textareaRef.value?.$el?.focus()
  textareaRef.value?.$el?.select()
})
</script>
