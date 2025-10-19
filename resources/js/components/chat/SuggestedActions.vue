<script setup lang="ts">
import { Button } from '@/components/ui/button'

const emit = defineEmits<{
  append: [message: string]
}>()

const suggestedActions = [
  {
    title: 'How do I create',
    label: 'a new Laravel project?',
    action: 'How do I create a new Laravel project from scratch?',
  },
  {
    title: 'Help me debug',
    label: 'my database connection',
    action: 'Help me debug database connection issues in Laravel',
  },
  {
    title: 'Explain the concept',
    label: 'of dependency injection',
    action: 'Explain the concept of dependency injection and how it works in Laravel',
  },
  {
    title: 'Show me how to',
    label: 'write clean code',
    action: 'Show me best practices for writing clean, maintainable code',
  },
]

function handleActionClick(suggestedAction: {
  title: string
  label: string
  action: string
}) {
  emit('append', suggestedAction.action)
}
</script>

<template>
  <div
    data-testid="suggested-actions"
    class="grid sm:grid-cols-2 gap-2 w-full"
  >
    <Transition
      v-for="(suggestedAction, index) in suggestedActions"
      :key="`suggested-action-${suggestedAction.title}-${index}`"
      appear
      :style="{ transitionDelay: `${0.05 * index}s` }"
      enter-active-class="transition-all duration-300"
      enter-from-class="opacity-0 translate-y-4"
      enter-to-class="opacity-1 translate-y-0"
    >
      <div :class="index > 1 ? 'hidden sm:block' : 'block'">
        <Button
          variant="ghost"
          class="text-left border rounded-xl px-4 py-3.5 text-sm flex-1 gap-1 sm:flex-col w-full h-auto justify-start items-start"
          @click="handleActionClick(suggestedAction)"
        >
          <span class="font-medium">{{ suggestedAction.title }}</span>
          <span class="text-muted-foreground">
            {{ suggestedAction.label }}
          </span>
        </Button>
      </div>
    </Transition>
  </div>
</template>
