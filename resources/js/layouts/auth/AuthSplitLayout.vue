<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import AppLogoIcon from '@/components/AppLogoIcon.vue'

defineProps<{
  title?: string
  description?: string
}>()
const page = usePage()
const name = page.props.name
</script>

<template>
  <div
    class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0"
  >
    <div
      class="relative hidden h-full flex-col bg-muted p-10 text-white dark:border-r lg:flex"
    >
      <div class="absolute inset-0 bg-zinc-900" />
      <Link
        :href="route('home')"
        class="relative z-20 flex items-center text-lg font-medium"
      >
        <AppLogoIcon class="mr-2 size-8 fill-current text-white" />
        {{ name }}
      </Link>
    </div>
    <div class="lg:p-8">
      <div
        class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]"
      >
        <div class="flex flex-col space-y-2 text-center">
          <h1 v-if="title" class="text-xl font-medium tracking-tight">
            {{ title }}
          </h1>
          <p v-if="description" class="text-sm text-muted-foreground">
            {{ description }}
          </p>
        </div>
        <slot />
      </div>
    </div>
  </div>
</template>
