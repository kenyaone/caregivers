<?php

namespace App\Http\Controllers\Caregiver;

use App\Http\Controllers\Controller;
use App\Models\Qualification;
use App\Models\User;
use App\Notifications\PlatformNotice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class QualificationController extends Controller
{
    public function index(Request $request)
    {
        return view('caregiver.qualifications', [
            'qualifications' => $request->user()->qualifications()->with('document')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_if($user->qualifications()->count() >= 15, 422, 'You can list up to 15 qualifications.');
        $data = $request->validate([
            'course' => 'required|string|max:150',
            'institution' => 'required|string|max:150',
            'level' => ['required', Rule::in(array_keys(Qualification::LEVELS))],
            'year' => 'nullable|integer|min:1970|max:'.now()->year,
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);
        $q = new Qualification(collect($data)->except('file')->all());
        $q->user_id = $user->id;
        $q->status = 'unverified';
        if ($file = $request->file('file')) {
            $q->document_id = $this->storeCertificate($user, $file)->id;
            $q->status = 'pending';
        }
        $q->save();
        if ($q->status === 'pending') {
            $this->alertAdmins($user, $q);
        }
        return back()->with('status', $q->status === 'pending' ? 'Qualification added. We will check the certificate.' : 'Qualification added. Upload the certificate when you have it to get it checked.');
    }

    /** Attach (or replace) the certificate for a qualification already listed. */
    public function certificate(Request $request, Qualification $qualification)
    {
        $user = $request->user();
        abort_unless($qualification->user_id === $user->id, 403);
        abort_if($qualification->status === 'approved', 422);
        $request->validate(['file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120']);
        $qualification->document_id = $this->storeCertificate($user, $request->file('file'))->id;
        $qualification->status = 'pending';
        $qualification->note = null;
        $qualification->save();
        $this->alertAdmins($user, $qualification);
        return back()->with('status', 'Certificate uploaded. We will check it.');
    }

    public function destroy(Request $request, Qualification $qualification)
    {
        abort_unless($qualification->user_id === $request->user()->id, 403);
        if ($qualification->document && $qualification->status !== 'approved') {
            Storage::disk('local')->delete($qualification->document->path);
            $qualification->document->delete();
        }
        $qualification->delete();
        return back()->with('status', 'Qualification removed.');
    }

    private function storeCertificate(User $user, $file)
    {
        return $user->documents()->create([
            'type' => 'certificate',
            'path' => $file->store('documents/'.$user->id, 'local'),
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 200),
        ]);
    }

    private function alertAdmins(User $user, Qualification $q): void
    {
        User::where('role', 'admin')->get()->each->notify(new PlatformNotice('College certificate to check',
            $user->name.' uploaded a '.$q->levelLabel().' in '.$q->course.' from '.$q->institution.'.', route('admin.qualifications')));
    }
}
