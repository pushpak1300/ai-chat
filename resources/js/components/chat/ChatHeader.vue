<script setup lang="ts">
import type { Visibility } from '@/types/enum'
import { Icon } from '@iconify/vue'
import { useWindowSize } from '@vueuse/core'
import ModelSelector from '@/components/chat/ModelSelector.vue'
import SidebarToggle from '@/components/chat/SidebarToggle.vue'
import VisibilitySelector from '@/components/chat/VisibilitySelector.vue'
import { Button } from '@/components/ui/button'
import { useSidebar } from '@/components/ui/sidebar'
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip'
import { provideVisibility } from '@/composables/useVisibility'

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

function handleNewChat() {
  window.location.href = '/'
}
</script>

<template>
  <header
    class="flex sticky top-0 bg-background py-1.5 items-center px-2 md:px-2 gap-2"
  >
    <SidebarToggle />

    <Tooltip v-if="!open || windowWidth.value < 768">
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

    <VisibilitySelector v-if="!isReadonly" class="order-1 md:order-3" />
  </header>
</template>
