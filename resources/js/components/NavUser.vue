<script setup lang="ts">
import { ChevronsUpDown } from 'lucide-vue-next'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  useSidebar,
} from '@/components/ui/sidebar'
import UserInfo from '@/components/UserInfo.vue'
import { useAuth } from '@/composables/useAuth'
import UserMenuContent from './UserMenuContent.vue'

const { user } = useAuth()
const { isMobile, state } = useSidebar()

const guestUser = {
  id: 0,
  name: 'Guest User',
  email: '',
  avatar: '',
  email_verified_at: null,
  created_at: '',
  updated_at: '',
}
</script>

<template>
  <SidebarMenu>
    <SidebarMenuItem>
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <SidebarMenuButton
            size="lg"
            class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
          >
            <UserInfo :user="user ?? guestUser" />
            <ChevronsUpDown class="ml-auto size-4" />
          </SidebarMenuButton>
        </DropdownMenuTrigger>
        <DropdownMenuContent
          class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg"
          :side="
            isMobile
              ? 'bottom'
              : state === 'collapsed'
                ? 'left'
                : 'bottom'
          "
          align="end"
          :side-offset="4"
        >
          <UserMenuContent :user="user ?? guestUser" />
        </DropdownMenuContent>
      </DropdownMenu>
    </SidebarMenuItem>
  </SidebarMenu>
</template>
