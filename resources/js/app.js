import Alpine from 'alpinejs';

import { editor } from './task-editor';
import { toast } from './toast';
import { theme } from './theme';

window.Alpine = Alpine;

Alpine.data('editor', editor);
Alpine.data('toast', toast);
Alpine.data('theme', theme);

Alpine.start();
