@extends('layouts.role')

@section('title', 'Dashboard Chief Judge')
@section('heading', 'Dashboard Chief Judge')

@section('content')
<div class="grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-xs text-slate-500">Menunggu Verifikasi</p>
        <p class="text-3xl font-bold">{{ $submitted }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-xs text-slate-500">Dibuka Ulang</p>
        <p class="text-3xl font-bold">{{ $reopened }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-xs text-slate-500">Sudah Disetujui</p>
        <p class="text-3xl font-bold">{{ $approved }}</p>
    </div>
</div>

<div class="mt-8 flex gap-3">
    <a href="{{ route('chief.verification.index') }}" class="rounded-lg bg-indigo-500 px-5 py-3 font-medium text-white hover:bg-indigo-400">Verifikasi Skor</a>
    <a href="{{ route('chief.publication.index') }}" class="rounded-lg border border-slate-300 px-5 py-3 font-medium hover:bg-slate-100">Publikasi Hasil</a>
</div>
@endsection
