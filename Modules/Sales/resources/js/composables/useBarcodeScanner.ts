import { onMounted, onUnmounted } from 'vue'

export function useBarcodeScanner(callback: (barcode: string) => void) {
  let buffer = ''
  let lastKeyTime = 0
  const threshold = 50 // ms between keystrokes (barcode scanners are faster)

  function handleKeydown(event: KeyboardEvent) {
    if (event.key === 'Enter') {
      if (buffer.length > 0) {
        callback(buffer)
        buffer = ''
      }
      return
    }

    const now = Date.now()
    if (now - lastKeyTime > threshold && buffer.length > 0) {
      buffer = ''
    }

    if (event.key.length === 1) {
      buffer += event.key
    }

    lastKeyTime = now
  }

  onMounted(() => {
    window.addEventListener('keydown', handleKeydown)
  })

  onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown)
  })
}
