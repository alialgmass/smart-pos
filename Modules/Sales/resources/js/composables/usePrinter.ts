export function usePrinter() {
  function printReceipt(url: string) {
    const iframe = document.createElement('iframe')
    iframe.style.position = 'absolute'
    iframe.style.width = '0'
    iframe.style.height = '0'
    iframe.style.border = 'none'
    document.body.appendChild(iframe)

    iframe.src = url

    iframe.onload = () => {
      try {
        iframe.contentWindow?.print()
      } catch {
        window.open(url, '_blank', 'width=400,height=600')
      }

      setTimeout(() => {
        document.body.removeChild(iframe)
      }, 1000)
    }
  }

  function printWindow(url: string) {
    window.open(url, '_blank', 'width=400,height=600')
  }

  return {
    printReceipt,
    printWindow,
  }
}
