<?php

namespace App\Http\Controllers\Caregiver;

use App\Http\Controllers\Controller;
use App\Models\CaregiverSkill;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Notifications\PlatformNotice;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SkillController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        return view('caregiver.skills', [
            'skills' => config('skills.skills'),
            'mine' => $user->skills()->get()->keyBy('skill'),
            'waits' => collect(array_keys(config('skills.skills')))->mapWithKeys(fn ($k) => [$k => $this->retryAt($user, $k)])->filter(),
        ]);
    }

    /** Save which skills the caregiver says they have. */
    public function declare(Request $request)
    {
        $user = $request->user();
        $keys = array_keys(config('skills.skills'));
        $data = $request->validate(['skills' => 'array', 'skills.*' => [Rule::in($keys)]]);
        $chosen = $data['skills'] ?? [];
        foreach ($keys as $k) {
            $row = $user->skills()->firstWhere('skill', $k);
            if (in_array($k, $chosen, true)) {
                $row ? $row->update(['declared' => true]) : $user->skills()->create(['skill' => $k, 'declared' => true]);
            } elseif ($row && ! $row->isEarned() && $row->cert_status !== 'pending') {
                $row->delete();
            }
        }
        return back()->with('status', 'Skills saved.');
    }

    public function quiz(Request $request, string $skill)
    {
        $def = $this->skill($skill);
        $user = $request->user();
        if ($user->skills()->where('skill', $skill)->whereNotNull('quiz_passed_at')->exists()) {
            return redirect()->route('caregiver.skills')->with('status', 'You already passed the '.$def['label'].' quiz.');
        }
        if ($wait = $this->retryAt($user, $skill)) {
            return redirect()->route('caregiver.skills')->withErrors(['quiz' => 'You can retake the '.$def['label'].' quiz '.$wait->diffForHumans().'.']);
        }
        // Show options in a random order; values are the original option numbers so the answer key never reaches the browser.
        $questions = collect($def['quiz'])->map(fn ($q, $i) => [
            'n' => $i,
            'q' => $q['q'],
            'options' => collect($q['options'])->map(fn ($o, $j) => ['v' => $j, 'text' => $o])->shuffle()->values()->all(),
        ]);
        return view('caregiver.quiz', ['key' => $skill, 'def' => $def, 'questions' => $questions, 'pass' => config('skills.pass_mark')]);
    }

    public function submit(Request $request, string $skill)
    {
        $def = $this->skill($skill);
        $user = $request->user();
        abort_if($user->skills()->where('skill', $skill)->whereNotNull('quiz_passed_at')->exists(), 422);
        if ($this->retryAt($user, $skill)) {
            return redirect()->route('caregiver.skills')->withErrors(['quiz' => 'Please wait before retaking this quiz.']);
        }
        $answers = $request->validate(['answers' => 'required|array'])['answers'];
        $total = count($def['quiz']);
        $missed = [];
        $score = 0;
        foreach ($def['quiz'] as $i => $q) {
            if (isset($answers[$i]) && (string) $answers[$i] === (string) $q['answer']) {
                $score++;
            } else {
                $missed[] = $q['q'];
            }
        }
        $passed = $score >= config('skills.pass_mark');
        QuizAttempt::create(['user_id' => $user->id, 'skill' => $skill, 'score' => $score, 'total' => $total, 'passed' => $passed]);
        if ($passed) {
            $row = CaregiverSkill::firstOrNew(['user_id' => $user->id, 'skill' => $skill]);
            $row->fill(['declared' => true, 'quiz_passed_at' => now(), 'quiz_score' => $score])->save();
        }
        return view('caregiver.quiz-result', [
            'key' => $skill, 'def' => $def, 'score' => $score, 'total' => $total, 'passed' => $passed,
            'missed' => $missed, 'retryHours' => config('skills.retry_hours'),
        ]);
    }

    public function certificate(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'skill' => ['required', Rule::in(array_keys(config('skills.skills')))],
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);
        $file = $request->file('file');
        $doc = $user->documents()->create([
            'type' => 'skill_cert',
            'path' => $file->store('documents/'.$user->id, 'local'),
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 200),
        ]);
        $row = CaregiverSkill::firstOrNew(['user_id' => $user->id, 'skill' => $data['skill']]);
        $row->forceFill(['declared' => true, 'cert_status' => 'pending', 'certificate_document_id' => $doc->id, 'cert_note' => null])->save();
        User::where('role', 'admin')->get()->each->notify(new PlatformNotice('Skill certificate to check', $user->name.' uploaded a '.$row->label().' certificate.', route('admin.skills')));
        return back()->with('status', 'Certificate uploaded. We will check it and add the badge.');
    }

    private function skill(string $key): array
    {
        abort_unless($def = config('skills.skills.'.$key), 404);
        return $def;
    }

    /** When a caregiver who failed recently may try again (null = now). */
    private function retryAt(User $user, string $skill): ?\Illuminate\Support\Carbon
    {
        $last = QuizAttempt::where('user_id', $user->id)->where('skill', $skill)->where('passed', false)->latest('id')->first();
        $at = $last?->created_at->copy()->addHours(config('skills.retry_hours'));
        return $at && $at->isFuture() ? $at : null;
    }
}
