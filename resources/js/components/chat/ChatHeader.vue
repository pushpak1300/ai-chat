<template>
  <header class="flex sticky top-0 bg-background py-1.5 items-center px-2 md:px-2 gap-2">
    <SidebarToggle />

    <Tooltip v-if="(!open || windowWidth.value < 768)">
      <TooltipTrigger as-child>
        <Button
          variant="outline"
          class="order-2 md:order-1 md:px-2 px-2 md:h-fit ml-auto md:ml-0"
          @click="handleNewChat"
        >
          <Icon icon="lucide:plus" />
          <span class="md:sr-only">New Chat</span>
        </Button>
      </TooltipTrigger>
      <TooltipContent>New Chat</TooltipContent>
    </Tooltip>

    <ModelSelector
      v-if="!isReadonly"
      :selected-model-id="selectedModelId"
      class="order-1 md:order-2"
    />

    <VisibilitySelector
      v-if="!isReadonly"
      class="order-1 md:order-3"
    />

    <Button
      class="bg-zinc-900 dark:bg-zinc-100 hover:bg-zinc-800 dark:hover:bg-zinc-200 text-zinc-50 dark:text-zinc-900 hidden md:flex py-1.5 px-2 h-fit md:h-[34px] order-4 md:ml-auto"
      as-child
    >
      <a
        href="https://github.com/your-repo"
        target="_blank"
        rel="noopener noreferrer"
      >
        <Icon icon="lucide:github" />
        View on GitHub
      </a>
    </Button>
  </header>
</template>

<script setup lang="ts">
import { useWindowSize } from '@vueuse/core'
import { useSidebar } from '@/components/ui/sidebar'
import { Button } from '@/components/ui/button'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import SidebarToggle from '@/components/chat/SidebarToggle.vue'
import ModelSelector from '@/components/chat/ModelSelector.vue'
import VisibilitySelector from '@/components/chat/VisibilitySelector.vue'
import { Icon } from '@iconify/vue'
import { provideVisibility } from '@/composables/useVisibility'
import { Visibility } from '@/types/enum'

interface Props {
  chatId: string
  selectedModelId: string
  selectedVisibilityType: Visibility
  isReadonly: boolean
}

const props = defineProps<Props>()

provideVisibility(props.selectedVisibilityType)

const { open } = useSidebar()
const { width: windowWidth } = useWindowSize()

const handleNewChat = () => {
  window.location.href = '/'
}
</script>
