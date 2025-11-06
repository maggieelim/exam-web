function showNotification(message, type = 'success') {
    const existingAlert = document.getElementById('successAlert') || document.getElementById('errorAlert');
    if (existingAlert) existingAlert.remove();

    const alert = document.createElement('div');
    alert.className = `alert alert-${type} fw-bold`;
    alert.id = type === 'success' ? 'successAlert' : 'errorAlert';
    alert.style.cssText =
        'position: fixed; top: 10%; right: 10px; max-width: fit-content; z-index: 9999; color: #ffffffff;';
    alert.innerHTML =
        type === 'success'
            ? `<div class="alert alert-success text-dark fw-bold" id="successAlert" style="position: fixed; top: 10%; right: 10px; max-width: fit-content; z-index: 9999; color: #ffffffff;">${message}</div>`
            : `<div class="alert alert-danger fw-bold" id="errorAlert" style="position: fixed; top: 10%; right: 10px; max-width: fit-content; z-index: 9999; color: #ffffffff;">${message}</div>`;

    document.body.appendChild(alert);

    setTimeout(() => {
        alert.remove();
    }, type === 'success' ? 5000 : 10000);
}
