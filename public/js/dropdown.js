document.addEventListener('DOMContentLoaded', function () {
    // --- BAGIAN 1: Dropdown kelompok (kode lama) ---
    const selects = document.querySelectorAll('.kelompok-select');
    if (selects.length) {
        selects.forEach(select => {
            select.addEventListener('change', updateDropdownOptions);
        });

        function updateDropdownOptions() {
            const selectedByScope = {};

            selects.forEach(select => {
                const scopeId = select.dataset.scopeId; // bisa skilllabId atau pemicuId
                const selectedValue = select.value;

                if (selectedValue) {
                    if (!selectedByScope[scopeId]) selectedByScope[scopeId] = [];
                    selectedByScope[scopeId].push(selectedValue);
                }
            });

            selects.forEach(select => {
                const scopeId = select.dataset.scopeId;
                const selectedValue = select.value;
                const options = select.querySelectorAll('option');

                options.forEach(option => {
                    if (option.value === '') return;
                    const isUsed = selectedByScope[scopeId]?.includes(option.value);
                    option.hidden = isUsed && option.value !== selectedValue;
                });
            });
        }

        updateDropdownOptions();
    }

    // --- BAGIAN 2: Clickable TD (klik td toggle checkbox) ---
    document.body.addEventListener('click', function (e) {
        const td = e.target.closest('.clickable-td');
        if (!td) return; // bukan di clickable td
        if (e.target.tagName.toLowerCase() === 'input') return; // klik langsung ke checkbox, abaikan

        const checkbox = td.querySelector('input[type="checkbox"]');
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event('change'));
        }
    });
});