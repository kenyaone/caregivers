@extends('layouts.app', ['title' => 'Documents'])
@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">Verification documents</h1>
    <p class="text-sm text-slate-600 mt-1">
        @if(auth()->user()->isCaregiver()) Upload your <b>national ID</b> and <b>police Certificate of Good Conduct</b> (both required), plus your training certificate and a reference letter if you have them. Families who unlock you can see your certificates and references, but never your ID card. @else Upload your national ID to get the "ID verified" badge. Only our team sees it. @endif
        PDF, JPG or PNG, max 5 MB.
    </p>
    <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="card mt-6 grid gap-4 md:grid-cols-3 items-end">
        @csrf
        <x-select name="type" label="Document type" :options="$types" required/>
        <div><label class="label" for="file">File</label><input id="file" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm" required></div>
        <button class="btn btn-primary">Upload</button>
    </form>
    <div class="card mt-6">
        <h2 class="font-semibold">Uploaded</h2>
        <div class="mt-3 divide-y">
            @forelse($documents as $d)
                <div class="py-3 flex items-center justify-between gap-3 text-sm">
                    <div><div class="font-semibold">{{ $d->label() }}</div><a class="link" href="{{ route('documents.download', $d) }}" target="_blank">{{ $d->original_name }}</a></div>
                    @if(auth()->user()->profile()?->verification_status !== 'approved')
                        <form method="POST" action="{{ route('documents.destroy', $d) }}" onsubmit="return confirm('Remove this document?')">@csrf @method('DELETE')<button class="text-red-600 text-sm">Remove</button></form>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-500">Nothing uploaded yet.</p>
            @endforelse
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-ghost mt-4">Back to dashboard</a>
    </div>
</div>
@endsection
