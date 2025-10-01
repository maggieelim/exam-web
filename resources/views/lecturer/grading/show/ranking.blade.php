 <!-- Ranking Siswa -->
 <div class="row mt-4">
   <div class="col-12">
     <div class="card">
       <div class="card-header d-flex justify-content-between align-items-center">
         <h5 class="card-title mb-0">
           <i class="fas fa-trophy me-2"></i>Ranking Siswa
         </h5>
         <span class="badge bg-primary">{{ count($rankingData) }} Peserta</span>
       </div>
       <div class="card-body">
         <div class="table-responsive">
           <table class="table table-bordered table-hover">
             <thead class="table-light">
               <tr>
                 <th width="8%" class="text-center">Peringkat</th>
                 <th width="35%">Nama Siswa</th>
                 <th width="15%" class="text-center">Jawaban Benar</th>
                 <th width="15%" class="text-center">Persentase</th>
                 <th width="15%" class="text-center">Status</th>
                 <th width="12%" class="text-center">Aksi</th>
               </tr>
             </thead>
             <tbody>
               @forelse($rankingData as $rank)
               <tr>
                 <td class="text-center">
                   @if($rank['rank'] == 1)
                   <span class="badge bg-warning text-dark">
                     <i class="fas fa-trophy me-1"></i>1
                   </span>
                   @elseif($rank['rank'] == 2)
                   <span class="badge bg-secondary">
                     <i class="fas fa-medal me-1"></i>2
                   </span>
                   @elseif($rank['rank'] == 3)
                   <span class="badge bg-orange">
                     <i class="fas fa-medal me-1"></i>3
                   </span>
                   @else
                   <span class="badge bg-light text-dark">
                     {{ $rank['rank'] }}
                   </span>
                   @endif
                 </td>
                 <td>{{ $rank['student_name'] }}</td>
                 <td class="text-center">
                   {{ $rank['correct_answers'] }}/{{ $rank['total_questions'] }}
                 </td>
                 <td class="text-center">
                   <span class="badge 
                                            {{ $rank['score_percentage'] >= 80 ? 'bg-success' : 
                                               ($rank['score_percentage'] >= 60 ? 'bg-info' : 
                                               ($rank['score_percentage'] >= 40 ? 'bg-warning' : 'bg-danger')) }}">
                     {{ number_format($rank['score_percentage'], 1) }}%
                   </span>
                 </td>
                 <td class="text-center">
                   <span class="badge 
                                            {{ $rank['attempt_status'] == 'completed' ? 'bg-success' : 'bg-warning' }}">
                     {{ $rank['attempt_status'] }}
                   </span>
                 </td>
                 <td class="text-center">
                   <button class="btn btn-sm btn-outline-primary">
                     <i class="fas fa-eye"></i>
                   </button>
                 </td>
               </tr>
               @empty
               <tr>
                 <td colspan="6" class="text-center text-muted py-4">
                   <i class="fas fa-info-circle me-2"></i>Tidak ada data ranking yang tersedia
                 </td>
               </tr>
               @endforelse
             </tbody>
           </table>
         </div>
       </div>
     </div>
   </div>
 </div>