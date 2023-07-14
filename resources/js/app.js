import './bootstrap';

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import Clipboard from "@ryangjchandler/alpine-clipboard";

window.Alpine = Alpine;

Alpine.plugin(focus);

Alpine.plugin(Clipboard);

Alpine.start();
