@extends('layouts.app', ['title' => 'Documents'])
@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">Verification documents</h1>
    <p class="text-sm text-slate-600 mt-1">
        @if(auth()->user()->isCaregiver()) Upload your <b>national ID</b>, a <b>selfie holding your ID</b> and your <b>police Certificate of Good Conduct</b> (all required; the certificate must be less than 6 months old), plus your training certificate and a reference letter if you have them. Families who unlock you can see your certificates and references, but never your ID card or selfie. @else Upload your national ID to get the "ID verified" badge. Only our team sees it. @endif
        PDF, JPG or PNG, max 5 MB.
    </p>
    @if(auth()->user()->isCaregiver() && ! $documents->contains('type', 'selfie'))
    <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="card mt-6" style="border-color:#5eead4;background:#f0fdfa">
        @csrf
        <input type="hidden" name="type" value="selfie">
        <h2 class="font-semibold">Take a selfie holding your ID</h2>
        <p class="text-sm text-slate-600 mt-1">Hold your national ID next to your face so both are clear. We compare it with your ID and profile photo, which stops anyone using someone else's ID. Only our team sees it.</p>
        <div class="mt-3 flex flex-wrap items-center gap-3">
            <input type="file" name="file" accept="image/*" capture="user" class="block text-sm" required>
            <button class="btn btn-primary">Upload selfie</button>
        </div>
    </form>
    @endif
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
