import './bootstrap';
import Alpine from 'alpinejs';
import gantt from './gantt';

Alpine.data('gantt', gantt);

window.Alpine = Alpine;
Alpine.start();
