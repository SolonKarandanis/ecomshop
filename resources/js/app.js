import './bootstrap';
import './product-detail-page';

import 'preline'

document.addEventListener('livewire:navigated', () => {
    window.HSStaticMethods.autoInit();
})
