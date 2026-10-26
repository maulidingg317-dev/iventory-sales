@php
    $labels = ['completed' => 'Selesai', 'confirmed' => 'Terkonfirmasi', 'cancelled' => 'Dibatalkan'];
    $color  = $status === 'cancelled' ? 'secondary' : 'success';
@endphp
<span class="badge text-bg-{{ $color }}">{{ $labels[$status] ?? $status }}</span>
