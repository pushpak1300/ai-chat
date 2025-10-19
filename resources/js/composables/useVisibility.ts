import type { InjectionKey, Ref } from 'vue'
import { inject, provide, readonly, ref, watch } from 'vue'
import { Visibility } from '@/types/enum'

interface VisibilityState {
  visibility: Readonly<Ref<Visibility>>
  setVisibility: (visibility: Visibility) => void
}

const VisibilityKey: InjectionKey<VisibilityState> = Symbol('visibility')

export function provideVisibility(
  initialVisibility: Visibility = Visibility.PRIVATE,
  syncRef?: Ref<Visibility>,
) {
  const visibility = ref<Visibility>(initialVisibility)

  const setVisibility = (newVisibility: Visibility) => {
    visibility.value = newVisibility
    if (syncRef) {
      syncRef.value = newVisibility
    }
  }

  if (syncRef) {
    watch(
      syncRef,
      (newValue) => {
        visibility.value = newValue
      },
      { immediate: false },
    )
  }

  const state: VisibilityState = {
    visibility: readonly(visibility),
    setVisibility,
  }

  provide(VisibilityKey, state)

  return state
}

export function useVisibility() {
  const state = inject(VisibilityKey)

  if (!state) {
    throw new Error(
      'useVisibility must be used within a component that provides visibility state',
    )
  }

  return state
}
