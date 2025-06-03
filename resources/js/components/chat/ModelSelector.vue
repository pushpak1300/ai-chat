<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { useStorage } from '@vueuse/core'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

const availableModels = [
  {
    id: 'gemini-2.0-flash',
    name: 'Gemini 2.0 Flash',
    description: 'Cheapest model, best for smarter tasks',
  },
  {
    id: 'gemini-2.0-flash-lite',
    name: 'Gemini 2.0 Flash Lite',
    description: 'Cheapest model, best for simpler tasks',
  },
]

const selectedModel = useStorage('selected-model', availableModels[0])

function selectModel(model: any) {
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
        {{ selectedModel.name }}
        <Icon icon="lucide:chevron-down" class="ml-auto" />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="start" class="min-w-[300px]">
      <DropdownMenuItem
        v-for="model in availableModels"
        :key="model.id"
        :data-testid="`model-selector-item-${model.id}`"
        @select="selectModel(model)"
      >
        <div class="flex flex-col gap-1 items-start">
          <div>{{ model.name }}</div>
          <div class="text-xs text-muted-foreground">
            {{ model.description }}
          </div>
        </div>
        <Icon v-if="model.id === selectedModel.id" icon="lucide:check-circle" class="ml-auto" />
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
