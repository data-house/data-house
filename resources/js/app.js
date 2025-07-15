import './bootstrap';

import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

import Clipboard from "@ryangjchandler/alpine-clipboard";

import Tooltip from "@ryangjchandler/alpine-tooltip";

import '@wotz/livewire-sortablejs';

import Anchor from '@alpinejs/anchor'
 

window.Alpine = Alpine;

Alpine.plugin(Clipboard);

Alpine.plugin(Anchor)

Alpine.plugin(Tooltip.defaultProps({
    theme: 'light',
    allowHTML: true,
}));

Livewire.start();
