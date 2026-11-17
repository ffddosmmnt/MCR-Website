// The same native dialog behavior as the supplied template; links also work without JS.
const dialog = document.getElementById('record-dialog');
if (dialog) {
    const returnUrl = dialog.querySelector('[aria-label="Close"]').href;
    dialog.removeAttribute('open');
    dialog.showModal();
    document.body.classList.add('modal-open');
    function closeDialog(event) {
        event.preventDefault();
        dialog.close();
        document.body.classList.remove('modal-open');
        history.replaceState(null, '', returnUrl);
        document.querySelector('[data-dialog-opener]')?.focus();
    }
    dialog.addEventListener('cancel', closeDialog);
    dialog.querySelectorAll('[aria-label="Close"], .modal-actions a').forEach(link => {
        link.addEventListener('click', closeDialog);
    });
}
