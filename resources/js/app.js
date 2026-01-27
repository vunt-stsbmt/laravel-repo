import './bootstrap';

window.addEventListener('DOMContentLoaded', () => {
    const copyButtons = document.querySelectorAll('[data-copy-target]');

    copyButtons.forEach((button) => {
        button.addEventListener('click', async () => {
            const targetId = button.getAttribute('data-copy-target');
            const target = targetId ? document.getElementById(targetId) : null;
            const value = target?.textContent?.trim();

            if (!value) {
                return;
            }

            try {
                await navigator.clipboard.writeText(value);
                const original = button.innerHTML;
                button.innerHTML = '<span class="h-2 w-2 rounded-full bg-emerald-400"></span>Đã sao chép';
                button.classList.add('text-emerald-200');

                setTimeout(() => {
                    button.innerHTML = original;
                    button.classList.remove('text-emerald-200');
                }, 1800);
            } catch (error) {
                console.error('Copy failed', error);
            }
        });
    });
});
