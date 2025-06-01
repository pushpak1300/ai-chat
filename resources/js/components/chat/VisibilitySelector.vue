<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button data-testid="visibility-selector" variant="outline" class="hidden md:flex md:px-2 md:h-[34px]">
                <component :is="selectedVisibility?.icon" />
                {{ selectedVisibility?.label }}
                <Icon icon="lucide:arrow-down" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="start" class="min-w-[300px]">
            <DropdownMenuItem v-for="visibility in visibilities" :key="visibility.id"
                :data-testid="`visibility-selector-item-${visibility.id}`" @select="selectVisibility(visibility)">
                <div class="flex flex-col gap-1 items-start">
                    {{ visibility.label }}
                    <div class="text-xs text-muted-foreground">
                        {{ visibility.description }}
                    </div>
                </div>
                <Icon v-if="visibility.id === selectedVisibilityType" class="ml-auto" icon="lucide:check" />
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger
} from '@/components/ui/dropdown-menu'
import { Icon, IconifyIcon } from '@iconify/vue'

interface Props {
    chatId: string
    selectedVisibilityType: 'private' | 'public'
}

const props = defineProps<Props>()

const visibilities: {
    id: 'private' | 'public'
    label: string
    description: string
    icon: IconifyIcon | string
}[] = [
        { id: 'private' as const, label: 'Private', description: 'Only you can access this chat', icon: 'lucide:lock' },
        { id: 'public' as const, label: 'Public', description: 'Anyone with the link can access this chat', icon: 'lucide:globe' }
    ]

const selectedVisibility = computed(() =>
    visibilities.find(visibility => visibility.id === props.selectedVisibilityType)
)

const selectVisibility = (visibility: any) => {
    console.log('Selected visibility:', visibility.id)
}
</script>
