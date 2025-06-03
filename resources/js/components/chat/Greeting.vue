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
        How can I help you today?
      </div>
    </Transition>
  </div>
</template>
