import './bootstrap';
import './product-detail-page';

import 'preline'
import Swal from 'sweetalert2'

window.Swal = Swal

document.addEventListener('livewire:navigated', () => {
    window.HSStaticMethods.autoInit();
})

window.Livewire.hook('morph.updated', ({ el, component }) => {
    window.HSStaticMethods.autoInit();
});
