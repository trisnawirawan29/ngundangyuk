@extends('layouts.admin')
@section('title', 'RSVP · '.$invitation->title)
@section('page-title', 'Respons RSVP')
@section('page-subtitle', $invitation->title)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><a href="{{ route('invitations.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-2"></i>Kembali</a><a href="{{ route('invitations.preview', $invitation) }}" target="_blank" class="btn btn-outline-secondary"><i class="fas fa-eye me-2"></i>Preview</a></div>
<div class="content-card"><div class="card-heading"><div><h5>Daftar Konfirmasi Kehadiran</h5><p>{{ $rsvps->count() }} respons masuk</p></div></div><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Nama</th><th>Status</th><th>Jumlah</th><th>Pesan</th><th>Waktu</th></tr></thead><tbody>@forelse($rsvps as $rsvp)<tr><td class="fw-semibold">{{ $rsvp->guest_name }}</td><td><span class="badge {{ $rsvp->attendance === 'hadir' ? 'text-bg-success' : ($rsvp->attendance === 'tidak_hadir' ? 'text-bg-danger' : 'text-bg-warning') }}">{{ str_replace('_', ' ', ucfirst($rsvp->attendance)) }}</span></td><td>{{ $rsvp->guest_count }} orang</td><td class="text-muted">{{ $rsvp->message ?: '—' }}</td><td class="text-muted small">{{ $rsvp->created_at->format('d/m/Y H:i') }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-5">Belum ada respons RSVP.</td></tr>@endforelse</tbody></table></div></div>
@endsection
