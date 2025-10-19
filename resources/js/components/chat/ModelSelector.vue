<script setup lang="ts">
import type { Model, SharedData } from '@/types'
import { Icon } from '@iconify/vue'
import { usePage } from '@inertiajs/vue3'
import { useStorage } from '@vueuse/core'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { MODEL_KEY } from '@/constants/models'

const page = usePage<SharedData>()
const selectedModel = useStorage<Model>(
  MODEL_KEY,
  page.props.availableModels?.[0],
)

function selectModel(model: Model) {
  selectedModel.value = model
}
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button
        data-testid="model-selector"
        variant="outline"
        class="md:px-2 md:h-[34px]"
      >
        {{ selectedModel?.name || "Select Model" }}
        <Icon icon="lucide:chevron-down" class="ml-auto" />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="start" class="min-w-[300px]">
      <DropdownMenuItem
        v-for="model in page.props.availableModels"
        :key="model.id"
        :data-testid="`model-selector-item-${model.id}`"
        @select="selectModel(model)"
      >
        <div class="flex flex-col gap-1 items-start">
          <div class="flex items-center gap-2">
            <span>{{ model?.name }}</span>
          </div>
          <div class="text-xs text-muted-foreground">
            {{ model?.description }}
          </div>
        </div>
        <Icon
          v-if="model?.id === selectedModel?.id"
          icon="lucide:check-circle"
          class="ml-auto"
        />
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
