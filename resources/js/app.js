import Alpine from 'alpinejs'
window.Alpine = Alpine

// WireUI registers Alpine directives/magic/stores inside its own deferred script.
// Starting Alpine on DOMContentLoaded guarantees WireUI finished registering before Alpine boots.
document.addEventListener('DOMContentLoaded', () => {
    Alpine.start()
})
