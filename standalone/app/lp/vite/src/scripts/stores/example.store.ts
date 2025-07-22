import Alpine from 'alpinejs'

// ストアの定義
Alpine.store('counter', {
  count: 0,
  increment() {
    this.count++
  },
  decrement() {
    this.count--
  }
})
