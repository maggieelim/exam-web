<div class="d-flex gap-2">
    <a class="btn btn-outline-info d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"
        href="{{ route('admin.courses.downloadPemicu', ['course' => $course->slug, 'semesterId' => $semesterId]) }}"
        title="Download Excel">
        <i class="fas fa-download"></i>
    </a>
</div>


<form class="schedule-form" action="{{ route('admin.course.assignPemicu') }}" method="POST">
    @csrf
    <div class="table-wrapper p-0">
        <input type="hidden" name="semester_id" value="{{ $semesterId }}">
        <input type="hidden" name="course_id" value="{{ $course->id }}">
        <table class="compact-table table-bordered">
            <thead class="text-center align-middle">
                <tr>
                    <th class="headcol view" rowspan="2">#</th>
                    <th class="headcol no" rowspan="2">No</th>
                    <th class="headcol name" rowspan="2">Nama Dosen</th>
                    @foreach ($pemicuData->tutors->take(ceil($pemicuData->tutors->count() / 2)) as $tutor)
                    <th colspan="2">Pemicu {{ $tutor->session_number }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($pemicuData->tutors as $tutor)
                    <th class="headrow text-wrap">
                        {{ $tutor->formatted_date }}<br>{{ \Carbon\Carbon::parse($tutor->start_time)->format('H:i')
                        }}<br>
                        {{ \Carbon\Carbon::parse($tutor->end_time)->format('H:i') }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($pemicuData->lecturers as $index => $lecturer)
                <tr>
                    <td class="text-center headcol view">
                        <a href="{{ route('schedules.index', ['lecturer_id' => $lecturer->lecturer->id]) }}"
                            class="text-info text-decoration-underline" title="Lihat Jadwal"> View
                        </a>
                    </td>
                    <td class="text-center headcol no">{{ $index + 1 }}</td>
                    <td class="headcol name">{{ $lecturer->lecturer->user->name }}</td>
                    @foreach ($pemicuData->tutors as $tutor)
                    @php
                    $isUnavailable = in_array(
                    $tutor->id,
                    $pemicuData->unavailableSlots[$lecturer->lecturer_id] ?? [],
                    );
                    $isAssigned = $tutor->pemicuDetails->contains('lecturer_id', $lecturer->lecturer_id);
                    $isDisabled = $isUnavailable && !$isAssigned;
                    $currentAssignment = $tutor->pemicuDetails->firstWhere(
                    'lecturer_id',
                    $lecturer->lecturer_id,
                    );
                    $currentKelompok = $currentAssignment ? $currentAssignment->kelompok_num : '';
                    @endphp

                    @if ($isDisabled && !$isAssigned)
                    <td class="text-center soft-info1">
                        -
                    </td>
                    @else
                    <td class="text-center">
                        <select class="form-select text-center input-bg kelompok-select"
                            name="assignments[{{ $lecturer->lecturer_id }}][{{ $tutor->id }}][kelompok]"
                            data-lecturer-id="{{ $lecturer->lecturer_id }}" data-tutor-id="{{ $tutor->id }}"
                            data-scope-id="{{ $tutor->id }}" data-pemicu-ke="{{ $tutor->pemicu_ke }}" {{ $isDisabled
                            ? 'disabled' : '' }}>
                            <option value=""></option>
                            @foreach ($pemicuData->kelompok ?? [] as $kel)
                            <option value="{{ $kel }}" {{ $currentKelompok==$kel ? 'selected' : '' }}>
                                {{ $kel }}
                            </option>
                            @endforeach
                        </select>
                    </td>
                    @endif
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3 d-flex justify-content-end">
        <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const kelompokSelects = document.querySelectorAll('.kelompok-select');
    
    // Function untuk mendapatkan pasangan pemicu
    function getPemicuPair(pemicuKe) {
        const pemicuNumber = Math.floor(pemicuKe / 10); 
        const pemicuOrder = pemicuKe % 10; 
        
        let pairPemicuKe;
        if (pemicuOrder === 1) {
            pairPemicuKe = (pemicuNumber * 10) + 2;
        } else if (pemicuOrder === 2) {
            pairPemicuKe = (pemicuNumber * 10) + 1;
        }
        return pairPemicuKe;
    }
    
    function triggerBackgroundChange(selectElement) {
        if (!selectElement) return;
        
        const changeEvent = new Event('change', { bubbles: true });
        selectElement.dispatchEvent(changeEvent);
        
        const inputEvent = new Event('input', { bubbles: true });
        selectElement.dispatchEvent(inputEvent);
    }

    function isKelompokAssignedToOtherLecturer(pemicuKe, kelompok, currentLecturerId) {
        // Cari semua select untuk pemicuKe ini
        const allSelectsForPemicu = document.querySelectorAll(
            `.kelompok-select[data-pemicu-ke="${pemicuKe}"]`
        );
        
        for (const select of allSelectsForPemicu) {
            // Skip select yang disabled atau milik dosen saat ini
            if (select.disabled || select.dataset.lecturerId === currentLecturerId) {
                continue;
            }
            
            // Jika ada dosen lain yang sudah mengajar kelompok ini
            if (select.value === kelompok) {
                return true;
            }
        }
        
        return false;
    }

    // Function untuk auto-fill pasangan pemicu
    function autoFillPemicuPair(triggerSelect, kelompokValue) {
        const lecturerId = triggerSelect.dataset.lecturerId;
        const currentPemicuKe = parseInt(triggerSelect.dataset.pemicuKe);
        
        // Jika kelompokValue adalah 0 (tidak mengajar), jangan auto-fill
        if (kelompokValue === "0") {
            return;
        }
        
        // Dapatkan pemicu pasangan
        const pairPemicuKe = getPemicuPair(currentPemicuKe);
        
        // Cek apakah kelompok sudah diajar dosen lain di pertemuan pasangan
        if (isKelompokAssignedToOtherLecturer(pairPemicuKe, kelompokValue, lecturerId)) {
            return;
        }
        
        // Cari select untuk pasangan pemicu yang sama (dosen yang sama)
        const pairSelect = document.querySelector(
            `.kelompok-select[data-lecturer-id="${lecturerId}"][data-pemicu-ke="${pairPemicuKe}"]`
        );
        
        if (pairSelect && !pairSelect.disabled) {
            // Jika pasangan belum terisi, auto-fill
            if (!pairSelect.value) {
                pairSelect.value = kelompokValue;
                
                // Beri visual feedback
                triggerBackgroundChange(pairSelect);
                
            }
        }
    }
    
    // Event listener untuk setiap select
    kelompokSelects.forEach(select => {
        select.addEventListener('change', function() {
            const kelompokValue = this.value;
            
            // Hanya proses jika ada nilai yang dipilih
            if (kelompokValue) {
                // Auto-fill ke pasangan pemicu
                autoFillPemicuPair(this, kelompokValue);
            }
        });
    });
    
   
});
</script>