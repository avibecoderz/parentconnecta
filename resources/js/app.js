import './bootstrap';

const canUseServiceWorker = 'serviceWorker' in navigator
    && (window.isSecureContext || ['localhost', '127.0.0.1', '[::1]'].includes(window.location.hostname));

if (canUseServiceWorker) {
    window.addEventListener('load', async () => {
        try {
            await navigator.serviceWorker.register('/sw.js', { scope: '/' });
        } catch (error) {
            console.error('Service worker registration failed:', error);
        }
    });
}

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    window.deferredPwaInstallPrompt = event;
    window.dispatchEvent(new CustomEvent('pwa:install-available'));
});

window.promptPwaInstall = async () => {
    const deferredPrompt = window.deferredPwaInstallPrompt;

    if (!deferredPrompt) {
        return false;
    }

    deferredPrompt.prompt();
    const choice = await deferredPrompt.userChoice;
    window.deferredPwaInstallPrompt = null;

    return choice.outcome === 'accepted';
};

window.addEventListener('appinstalled', () => {
    window.deferredPwaInstallPrompt = null;
    window.dispatchEvent(new CustomEvent('pwa:installed'));
});
