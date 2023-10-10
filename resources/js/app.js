import './bootstrap';

import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

import Clipboard from "@ryangjchandler/alpine-clipboard";

window.Alpine = Alpine;

Alpine.plugin(Clipboard);

Livewire.start();
