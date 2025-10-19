<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { useVisibility } from '@/composables/useVisibility'
import { Visibility } from '@/types/enum'

interface VisibilityOption {
  id: Visibility
  label: string
  description: string
  icon: string
}

const { visibility, setVisibility } = useVisibility()

const availableVisibilities: VisibilityOption[] = [
  {
    id: Visibility.PRIVATE,
    label: 'Private',
    description: 'Only you can access this chat',
    icon: 'lucide:lock',
  },
  {
    id: Visibility.PUBLIC,
    label: 'Public',
    description: 'Anyone with the link can access this chat',
    icon: 'lucide:globe',
  },
]

const selectedVisibility = computed(
  () =>
    availableVisibilities.find(v => v.id === visibility.value)
    || availableVisibilities[0],
)

function selectVisibility(visibilityOption: VisibilityOption) {
  setVisibility(visibilityOption.id)
}
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button
        data-testid="visibility-selector"
        variant="outline"
        class="md:px-2 md:h-[34px]"
      >
        <Icon :icon="selectedVisibility.icon" class="mr-2" />
        {{ selectedVisibility.label }}
        <Icon icon="lucide:chevron-down" class="ml-auto" />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="start" class="min-w-[300px]">
      <DropdownMenuItem
        v-for="visibilityOption in availableVisibilities"
        :key="visibilityOption.id"
        :data-testid="`visibility-selector-item-${visibilityOption.id}`"
        @select="selectVisibility(visibilityOption)"
      >
        <div class="flex flex-col gap-1 items-start">
          <div class="flex items-center gap-2">
            <Icon :icon="visibilityOption.icon" />
            {{ visibilityOption.label }}
          </div>
          <div class="text-xs text-muted-foreground">
            {{ visibilityOption.description }}
          </div>
        </div>
        <Icon
          v-if="visibilityOption.id === visibility"
          icon="lucide:check-circle"
          class="ml-auto"
        />
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
