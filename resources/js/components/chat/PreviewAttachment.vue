<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { Button } from '@/components/ui/button'

interface Props {
  attachment: Array<string>
  isUploading?: boolean
  showRemove?: boolean
}

withDefaults(defineProps<Props>(), {
  isUploading: false,
  showRemove: true,
})

defineEmits<{
  remove: []
}>()

function getFileExtension(filename: string) {
  const ext = filename.split('.').pop()?.toUpperCase()
  return ext || 'FILE'
}
</script>

<template>
  <div class="relative group">
    <div
      class="flex flex-col items-center justify-center w-20 h-20 bg-muted rounded-lg border border-border overflow-hidden"
    >
      <div
        v-if="isUploading"
        class="flex items-center justify-center w-full h-full"
      >
        <div
          class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"
        />
      </div>

      <img
        :src="attachment[0]"
        :alt="attachment[0]"
        class="w-full h-full object-cover"
      >

      <div
        class="flex flex-col items-center justify-center w-full h-full text-muted-foreground"
      >
        <Icon icon="lucide:file" class="w-6 h-6 mb-1" />
        <span class="text-xs truncate w-full text-center px-1">
          {{ getFileExtension(attachment[0]) }}
        </span>
      </div>
    </div>

    <div
      class="mt-1 text-xs text-center text-muted-foreground truncate w-20"
    >
      {{ attachment[0] }}
    </div>

    <Button
      v-if="!isUploading && showRemove"
      variant="destructive"
      size="icon"
      class="absolute -top-2 -right-2 w-5 h-5 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
      @click="$emit('remove')"
    >
      <Icon icon="lucide:x" class="w-3 h-3" />
    </Button>
  </div>
</template>
