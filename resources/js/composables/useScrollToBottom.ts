import { ref, computed } from 'vue'
import { useScroll } from '@vueuse/core'

export function useScrollToBottom() {
  const containerRef = ref<HTMLElement>()

  const { y, arrivedState, measure } = useScroll(containerRef, {
    behavior: 'auto'
  })

  const isAtBottom = computed(() => arrivedState.bottom)

  const scrollToBottom = (behavior: ScrollBehavior = 'smooth') => {
    if (!containerRef.value) return

    const maxScrollTop = containerRef.value.scrollHeight - containerRef.value.clientHeight

    if (behavior === 'auto') {
      y.value = maxScrollTop
    } else {
      containerRef.value.scrollTo({
        top: maxScrollTop,
        behavior
      })
    }
  }

  const scrollToBottomInstant = () => {
    scrollToBottom('auto')
  }

  const scrollToBottomSmooth = () => {
    scrollToBottom('smooth')
  }

  return {
    containerRef,
    isAtBottom,
    scrollToBottom,
    scrollToBottomInstant,
    scrollToBottomSmooth,
    measure
  }
}
