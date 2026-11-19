<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Unlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        return view('shared.documents', [
            'documents' => $user->documents()->latest()->get(),
            'types' => config('noc.document_types.'.($user->isCaregiver() ? 'caregiver' : 'family')),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $types = array_keys(config('noc.document_types.'.($user->isCaregiver() ? 'caregiver' : 'family')));
        $request->validate([
            'type' => 'required|in:'.implode(',', $types),
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);
        $file = $request->file('file');
        $user->documents()->create([
            'type' => $request->type,
            'path' => $file->store('documents/'.$user->id, 'local'),
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 200),
        ]);
        return back()->with('status', 'Document uploaded.');
    }

    public function destroy(Request $request, Document $document)
    {
        abort_unless($document->user_id === $request->user()->id, 403);
        abort_if($request->user()->profile()?->verification_status === 'approved', 422, 'Verified accounts cannot remove documents. Contact support.');
        Storage::disk('local')->delete($document->path);
        $document->delete();
        return back()->with('status', 'Document removed.');
    }

    public function download(Request $request, Document $document)
    {
        $user = $request->user();
        $allowed = $user->isAdmin() || $document->user_id === $user->id
            // families may see the documents of caregivers whose contacts they unlocked (not the ID card)
            || ($user->isFamily() && $document->type !== 'national_id'
                && Unlock::where('family_id', $user->id)->where('caregiver_id', $document->user_id)->exists());
        abort_unless($allowed, 403);
        return Storage::disk('local')->response($document->path, $document->original_name);
    }
}
