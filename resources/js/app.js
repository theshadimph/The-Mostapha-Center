import appearance from './appearance';

document.addEventListener('alpine:init', () => {
    appearance(window.Alpine);
});
