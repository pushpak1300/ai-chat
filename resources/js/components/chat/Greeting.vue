<script setup lang="ts">
import { computed } from 'vue'
import { useAuth } from '@/composables/useAuth'

const { isGuest } = useAuth()

const subtitle = computed(() =>
  isGuest.value ? 'Please login to continue.' : 'How can I help you today?',
)
</script>

<template>
  <div class="max-w-3xl mx-auto px-8 size-full flex flex-col justify-center">
    <Transition
      appear
      enter-active-class="transition-all duration-200"
      enter-from-class="opacity-0 translate-y-2"
      enter-to-class="opacity-1 translate-y-0"
    >
      <div class="text-2xl font-semibold">
        Hello there!
      </div>
    </Transition>

    <Transition
      appear
      enter-active-class="transition-all duration-200"
      enter-from-class="opacity-0 translate-y-2"
      enter-to-class="opacity-1 translate-y-0"
    >
      <div class="text-2xl text-zinc-500">
        {{ subtitle }}
      </div>
    </Transition>
  </div>
</template>
