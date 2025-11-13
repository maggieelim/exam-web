// schedule-ajax.js - Reusable AJAX untuk semua form schedule dengan custom handlers

class ScheduleAjaxHandler {
    constructor() {
        this.customHandlers = new Map();
        this.init();
    }

    init() {
        // Event delegation untuk semua form dengan class .schedule-form
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.classList.contains('schedule-form')) {
                e.preventDefault();
                this.handleFormSubmit(form);
            }
        });

        // Event delegation untuk tombol delete yang sudah ada
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('delete-schedule') || 
                e.target.closest('.delete-schedule')) {
                e.preventDefault();
                const deleteBtn = e.target.classList.contains('delete-schedule') ? 
                    e.target : e.target.closest('.delete-schedule');
                
                // Hanya proses jika ada data-id (tombol aktif)
                if (deleteBtn.getAttribute('data-id')) {
                    this.handleDelete(deleteBtn);
                }
            }
        });

        // Register custom handlers
        this.registerCustomHandlers();
    }

    registerCustomHandlers() {
        // Register practicum form handler
        this.customHandlers.set('practicumForm', {
            beforeSubmit: (form) => this.practicumBeforeSubmit(form),
            afterSuccess: (data, form) => this.practicumAfterSuccess(data, form),
            afterDelete: (scheduleId) => this.practicumAfterDelete(scheduleId)
        });

        // Bisa register handler untuk form lainnya di sini
        // this.customHandlers.set('skillslabForm', { ... });
    }

    handleFormSubmit(form) {
        const formId = form.id;
        const customHandler = this.customHandlers.get(formId);
        
        // Jalankan custom beforeSubmit jika ada
        if (customHandler && customHandler.beforeSubmit) {
            customHandler.beforeSubmit(form);
        }

        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;

         form.querySelectorAll('.schedule-error').forEach(el => el.remove());
         form.querySelectorAll('tr.table-danger').forEach(el => el.classList.remove('table-danger'));

        // Show loading state
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        submitButton.disabled = true;

        fetch(form.action, {
            method: "POST",
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": this.getCsrfToken()
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Schedule response received:', data);

            if (data.success) {
                if (typeof showNotification === 'function') {
                    showNotification(data.message, "success");
                } else {
                    this.fallbackNotification(data.message, "success");
                }

                 if (data.failed_schedules && data.failed_schedules.length > 0) {
                data.failed_schedules.forEach(failed => {
                    const row = form.querySelector(`input[name="schedules[${failed.schedule_id}][id]"]`)?.closest('tr');
                    if (row) {
                        row.classList.add('table-danger');

                        const errorRow = document.createElement('tr');
                        errorRow.classList.add('schedule-error');
                        errorRow.innerHTML = `
                            <td colspan="12" class="text-danger text-end fw-semibold bg-light">
                                 ${failed.message}
                            </td>
                        `;
                        row.insertAdjacentElement('afterend', errorRow);
                    }
                });

                // Scroll ke error pertama
                const firstError = form.querySelector('.table-danger');
                if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
                // Jalankan custom afterSuccess jika ada
                if (customHandler && customHandler.afterSuccess) {
                    customHandler.afterSuccess(data, form);
                } else {
                    // Default behavior
                    this.updateUIAfterSuccess(data.updated_schedules, form);
                }

            } else {
                if (typeof showNotification === 'function') {
                    showNotification(data.message || "Gagal menyimpan data", "error");
                } else {
                    this.fallbackNotification(data.message || "Gagal menyimpan data", "error");
                }
            }
        })
        .catch(err => {
            console.error('Schedule fetch error:', err);
            if (typeof showNotification === 'function') {
                showNotification("Terjadi kesalahan saat memperbarui data: " + err.message, "error");
            } else {
                this.fallbackNotification("Terjadi kesalahan saat memperbarui data: " + err.message, "error");
            }
        })
        .finally(() => {
            // Restore button
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        });
    }

    handleDelete(deleteBtn) {
        const scheduleId = deleteBtn.getAttribute('data-id');
        if (!scheduleId) {
            console.warn('Delete button has no data-id attribute');
            return;
        }

        if (!confirm('Apakah kamu yakin ingin mengosongkan jadwal ini?')) return;

        const originalContent = deleteBtn.innerHTML;
        const token = this.getCsrfToken();
        
        // Show loading
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        deleteBtn.style.pointerEvents = 'none';

        fetch(`/admin/course/schedules/${scheduleId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (typeof showNotification === 'function') {
                    showNotification(data.message, 'success');
                } else {
                    this.fallbackNotification(data.message, 'success');
                }

                // Cari form parent untuk menentukan custom handler
                const form = deleteBtn.closest('.schedule-form');
                const formId = form ? form.id : null;
                const customHandler = formId ? this.customHandlers.get(formId) : null;

                // Jalankan custom afterDelete jika ada
                if (customHandler && customHandler.afterDelete) {
                    customHandler.afterDelete(scheduleId);
                } else {
                    // Default behavior
                    this.defaultAfterDelete(deleteBtn, scheduleId);
                }

            } else {
                if (typeof showNotification === 'function') {
                    showNotification(data.message || 'Gagal menghapus schedule', 'error');
                } else {
                    this.fallbackNotification(data.message || 'Gagal menghapus schedule', 'error');
                }
            }
        })
        .catch(err => {
            console.error(err);
            if (typeof showNotification === 'function') {
                showNotification('Terjadi kesalahan saat menghapus schedule', 'error');
            } else {
                this.fallbackNotification('Terjadi kesalahan saat menghapus schedule', 'error');
            }
        })
        .finally(() => {
            deleteBtn.innerHTML = originalContent;
            deleteBtn.style.pointerEvents = 'auto';
        });
    }

    // ========== CUSTOM HANDLERS ==========

    // Practicum Form Custom Handler
    practicumBeforeSubmit(form) {
        console.log('Practicum form before submit');
        // Tambahkan logic sebelum submit jika diperlukan
    }

    practicumAfterSuccess(data, form) {
        console.log('Practicum form after success');
        
        // Panggil fungsi regroupSchedules
        this.regroupSchedules();
        
        // Reset perubahan visual
        form.querySelectorAll('.soft-edit').forEach(td => {
            td.classList.remove('soft-edit');
        });

        // Update data-original untuk semua input
        form.querySelectorAll('.input-bg').forEach(input => {
            if (input.type === 'checkbox') {
                input.dataset.original = input.checked ? 'true' : 'false';
            } else if (input.type === 'text' || input.type === 'date') {
                input.dataset.original = input.value;
            } else if (input.tagName === 'SELECT') {
                input.dataset.original = input.value;
            }
        });

        // Update schedule fields
        if (Array.isArray(data.updated_schedules)) {
            data.updated_schedules.forEach(s => {
                this.updateScheduleFields(s);
            });
        }

        // Re-initialize collapse functionality
        this.initializeCollapse();
    }

    practicumAfterDelete(scheduleId) {
        console.log('Practicum after delete:', scheduleId);
        
        // Default delete behavior
        const deleteBtn = document.querySelector(`.delete-schedule[data-id="${scheduleId}"]`);
        if (deleteBtn) {
            this.defaultAfterDelete(deleteBtn, scheduleId);
        }
        
        // Regroup setelah delete
        setTimeout(() => {
            this.regroupSchedules();
            this.initializeCollapse();
        }, 100);
    }

    // Practicum Specific Functions
    regroupSchedules() {
        const scheduleTbody = document.getElementById("schedule-tbody");
        if (!scheduleTbody) return;

        const rows = Array.from(scheduleTbody.querySelectorAll('.schedule-row'));
        const topicGroups = {};

        // Kelompokkan jadwal berdasarkan topik
        rows.forEach(row => {
            const topicInput = row.querySelector('.topic-input');
            const topic = topicInput ? topicInput.value.trim().toLowerCase() : 'tanpa topik';
            const scheduleId = row.dataset.scheduleId;

            if (!topicGroups[topic]) {
                topicGroups[topic] = [];
            }
            topicGroups[topic].push({
                row: row,
                scheduleId: scheduleId
            });
        });

        // Hapus semua rows dari tbody
        while (scheduleTbody.firstChild) {
            scheduleTbody.removeChild(scheduleTbody.firstChild);
        }

        // Tambahkan kembali rows yang sudah dikelompokkan
        Object.keys(topicGroups).sort().forEach(topic => {
            const schedules = topicGroups[topic];

            // Buat header topik
            const topicHeader = document.createElement('tr');
            topicHeader.className = 'topic-group';
            topicHeader.innerHTML = `
                <td colspan="9" class="group-header" data-bs-toggle="collapse" data-bs-target="#topic-${this.slugify(topic)}">
                    <i class="fas fa-caret-down collapse-icon me-2"></i>
                    <strong> Topik: <span class="topic-display">${topic}</span></strong>
                </td>
            `;
            scheduleTbody.appendChild(topicHeader);

            // Tambahkan rows jadwal untuk topik ini
            schedules.forEach((schedule, index) => {
                const row = schedule.row;
                row.id = `topic-${this.slugify(topic)}`;
                row.className = `collapse show schedule-row`;
                row.dataset.originalTopic = topic;

                // Update nomor urut
                const numberCell = row.querySelector('td:first-child');
                if (numberCell) {
                    numberCell.textContent = index + 1;
                }

                scheduleTbody.appendChild(row);
            });
        });
    }

    slugify(text) {
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    }

    initializeCollapse() {
        const groupHeaders = document.querySelectorAll('.group-header');
        groupHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const target = this.getAttribute('data-bs-target');
                const icon = this.querySelector('.collapse-icon');

                if (icon.classList.contains('fa-caret-down')) {
                    icon.classList.remove('fa-caret-down');
                    icon.classList.add('fa-caret-right');
                } else {
                    icon.classList.remove('fa-caret-right');
                    icon.classList.add('fa-caret-down');
                }
            });
        });

        // Event listener untuk input topik (real-time update)
        document.addEventListener('input', (e) => {
            if (e.target.classList.contains('topic-input')) {
                const scheduleId = e.target.dataset.scheduleId;
                const newTopic = e.target.value.trim();

                const scheduleRow = document.querySelector(
                    `.schedule-row[data-schedule-id="${scheduleId}"]`);
                if (scheduleRow) {
                    const originalTopic = scheduleRow.dataset.originalTopic;
                    const topicGroup = document.querySelector(
                        `.topic-group:has(.topic-display:contains('${originalTopic}')`);

                    if (topicGroup) {
                        const topicDisplay = topicGroup.querySelector('.topic-display');
                        if (topicDisplay) {
                            topicDisplay.textContent = newTopic || 'Tanpa Topik';
                        }
                    }

                    scheduleRow.dataset.originalTopic = newTopic || 'Tanpa Topik';
                }
            }
        });
    }

    // ========== DEFAULT BEHAVIORS ==========

    defaultAfterDelete(deleteBtn, scheduleId) {
        // Reset visual input pada baris yang dihapus
        const row = deleteBtn.closest('tr');
        if (row) {
            row.querySelectorAll('input').forEach(input => input.value = '');
            row.querySelectorAll('select').forEach(select => select.value = '');
            
            // Update tombol delete menjadi non-aktif
            this.updateDeleteButtonVisibility(scheduleId, null);
        }
    }

    updateUIAfterSuccess(updatedSchedules, form) {
        // Default behavior untuk form tanpa custom handler
        form.querySelectorAll('.soft-edit').forEach(td => {
            td.classList.remove('soft-edit');
        });

        form.querySelectorAll('.input-bg').forEach(input => {
            if (input.type === 'checkbox') {
                input.dataset.original = input.checked ? 'true' : 'false';
            } else if (input.type === 'text' || input.type === 'date') {
                input.dataset.original = input.value;
            } else if (input.tagName === 'SELECT') {
                input.dataset.original = input.value;
            }
        });

        if (Array.isArray(updatedSchedules)) {
            updatedSchedules.forEach(schedule => {
                this.updateScheduleFields(schedule);
            });
        }
    }

    updateScheduleFields(schedule) {
        const fields = [
            { id: `start_time_${schedule.id}`, value: schedule.start_time?.substring(0, 5) || '' },
            { id: `end_time_${schedule.id}`, value: schedule.end_time?.substring(0, 5) || '' },
            { id: `group_${schedule.id}`, value: schedule.group || '' },
            { id: `scheduled_date_${schedule.id}`, value: schedule.scheduled_date || '' },
            { id: `zone_${schedule.id}`, value: schedule.zone || '' }
        ];

        fields.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) {
                element.value = field.value;
            }
        });

        this.updateDeleteButtonVisibility(schedule.id, schedule.zone);
    }

    updateDeleteButtonVisibility(scheduleId, zone) {
        const row = document.querySelector(`input[name="schedules[${scheduleId}][id]"]`)?.closest('tr');
        if (!row) return;
        
        const deleteTd = row.querySelector('td:nth-child(2)');
        if (!deleteTd) return;
        
        if (zone !== null && zone !== '') {
            deleteTd.innerHTML = `
                <a href="#" class="delete-schedule text-danger text-decoration-underline" 
                   data-id="${scheduleId}">DEL</a>
            `;
        } else {
            deleteTd.innerHTML = `
                <a class="delete-schedule text-danger">DEL</a>
            `;
        }
    }

    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
               document.querySelector('input[name="_token"]')?.value;
    }

    fallbackNotification(message, type = "info") {
        alert(`${type.toUpperCase()}: ${message}`);
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    new ScheduleAjaxHandler();
});