<script setup lang="ts">
import { Icon } from '@iconify/vue'
</script>

<template>
  <Transition
    appear
    enter-active-class="transition-all duration-500 delay-1000"
    enter-from-class="opacity-0 translate-y-1"
    enter-to-class="opacity-1 translate-y-0"
  >
    <div
      data-testid="message-assistant-loading"
      class="w-full mx-auto max-w-3xl px-4 group/message min-h-96"
      data-role="assistant"
    >
      <div class="flex gap-4 w-full">
        <div
          class="size-8 flex items-center rounded-full justify-center ring-1 shrink-0 ring-border"
        >
          <Icon icon="lucide:sparkles" class="size-3.5" />
        </div>

        <div class="flex flex-col gap-2 w-full">
          <div class="flex flex-col gap-4 text-muted-foreground">
            Hmm...
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>
