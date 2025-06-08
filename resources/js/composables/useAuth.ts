import type { SharedData, User } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function useAuth() {
  const page = usePage<SharedData>()

  const user = computed<User | null>(() => page.props.auth.user ?? null)

  const isGuest = computed(() => !user.value)

  const isAuthenticated = computed(() => !!user.value)

  return {
    user,
    isGuest,
    isAuthenticated,
  }
}
