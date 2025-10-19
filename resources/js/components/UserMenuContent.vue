<script setup lang="ts">
import type { User } from '@/types'
import { Link, router } from '@inertiajs/vue3'
import { LogIn, LogOut, Settings } from 'lucide-vue-next'
import {
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu'
import UserInfo from '@/components/UserInfo.vue'
import { useAuth } from '@/composables/useAuth'

interface Props {
  user: User
}

defineProps<Props>()

const { isGuest } = useAuth()

function handleLogout() {
  router.flushAll()
}
</script>

<template>
  <DropdownMenuLabel class="p-0 font-normal">
    <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
      <UserInfo :user="user" :show-email="!isGuest" />
    </div>
  </DropdownMenuLabel>
  <DropdownMenuSeparator />

  <template v-if="isGuest">
    <DropdownMenuGroup>
      <DropdownMenuItem :as-child="true">
        <Link class="block w-full" :href="route('login')" as="button">
          <LogIn class="mr-2 h-4 w-4" />
          Login
        </Link>
      </DropdownMenuItem>
    </DropdownMenuGroup>
  </template>

  <template v-else>
    <DropdownMenuGroup>
      <DropdownMenuItem :as-child="true">
        <Link
          class="block w-full"
          :href="route('profile.edit')"
          prefetch
          as="button"
        >
          <Settings class="mr-2 h-4 w-4" />
          Settings
        </Link>
      </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
      <Link
        class="block w-full"
        method="post"
        :href="route('logout')"
        as="button"
        @click="handleLogout"
      >
        <LogOut class="mr-2 h-4 w-4" />
        Log out
      </Link>
    </DropdownMenuItem>
  </template>
</template>
