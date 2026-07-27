import './bootstrap';
import './product-detail-page';

import 'preline'
import Swal from 'sweetalert2'
import collapse from '@alpinejs/collapse'

window.Swal = Swal

document.addEventListener('alpine:init', () => {
    Alpine.plugin(collapse)
})

document.addEventListener('livewire:navigated', () => {
    window.HSStaticMethods.autoInit();
})

window.Livewire.hook('morph.updated', ({ el, component }) => {
    window.HSStaticMethods.autoInit();
});
