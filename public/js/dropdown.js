document.addEventListener('DOMContentLoaded', function () {
    const selects = document.querySelectorAll('.kelompok-select');
    if (!selects.length) return;

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
});
