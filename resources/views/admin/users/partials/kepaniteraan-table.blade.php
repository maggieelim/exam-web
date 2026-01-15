<div class="col-12 ">
    <h6 class="text-dark mb-3">Daftar Kepaniteraan</h6>
    <div class="table-responsive">
        <table class="table align-items-center mb-0">
            <thead>
                <tr>
                    <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Rumah Sakit</th>
                    <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Stase</th>
                    <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Semester</th>
                    <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Periode</th>
                    <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kepaniteraan as $koas)
                <tr>
                    <td class="align-middle text-center">{{ $koas->hospitalRotation->hospital->name }}</td>
                    <td class="align-middle text-center">{{ $koas->hospitalRotation->clinicalRotation->name }}</td>
                    <td class="align-middle text-center">{{ $koas->semester->semester_name }} {{
                        $koas->semester->academicYear->year_name }}</td>
                    <td class="align-middle text-center">{{ $koas->start_date->format('d M Y') }} - {{
                        $koas->end_date->format('d M Y') }}</td>
                    <td class="align-middle text-center">{{ ucfirst($koas->status) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center mt-3">
            <x-pagination :paginator="$kepaniteraan" />
        </div>
    </div>
</div>