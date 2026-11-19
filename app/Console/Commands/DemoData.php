<?php

namespace App\Console\Commands;

use App\Models\CareRequest;
use App\Models\CaregiverSkill;
use App\Models\CaregiverProfile;
use App\Models\Consent;
use App\Models\ContactPass;
use App\Models\FamilyProfile;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Review;
use App\Models\Unlock;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/** Demo data for testing. All demo accounts use @demo.test emails; --purge removes them. */
class DemoData extends Command
{
    protected $signature = 'noc:demo {--purge : Delete all demo accounts and their data} {--password=Demo@2026}';

    protected $description = 'Create (or purge) demo caregivers, families, jobs and requests';

    public function handle(): int
    {
        $this->purge();
        if ($this->option('purge')) {
            $this->info('Demo data removed.');
            return self::SUCCESS;
        }
        $pw = $this->option('password');
        $cgPlan = Plan::where('audience', 'caregiver')->where('is_active', true)->firstOrFail();

        DB::transaction(function () use ($pw, $cgPlan) {
            $cgs = [];
            $defs = [
                ['Grace Njeri', 'Experienced elderly & dementia caregiver', ['elderly', 'dementia', 'bedridden'], ['live_in', 'day'], 'Nairobi', 'Kileleshwa', 'female', 38, 8, 22000, 'English, Kiswahili, Kikuyu', 'Caregiver certificate'],
                ['Mercy Achieng', 'Loving nanny for babies & toddlers', ['children', 'special_needs'], ['day', 'live_in'], 'Nairobi', 'South B', 'female', 29, 5, 15000, 'English, Kiswahili, Dholuo', 'ECDE certificate; First Aid'],
                ['Peter Kiprono', 'Male caregiver: mobility & stroke rehab', ['disability', 'rehab', 'bedridden'], ['live_in', 'night'], 'Uasin Gishu', 'Eldoret', 'male', 34, 6, 20000, 'English, Kiswahili, Kalenjin', 'Caregiver certificate'],
                ['Faith Wambui', 'Live-in caregiver & house manager', ['live_in', 'house_help', 'elderly'], ['live_in'], 'Kiambu', 'Ruiru', 'female', 42, 12, 18000, 'English, Kiswahili, Kikuyu', 'Home-based care training'],
                ['Janet Chepkemoi', 'Palliative & end-of-life care support', ['palliative', 'bedridden', 'elderly'], ['day', 'night', 'hourly'], 'Uasin Gishu', 'Eldoret', 'female', 45, 15, 1200, 'English, Kiswahili, Nandi', 'Palliative care course; CNA'],
                ['Halima Omar', 'Caring nanny & special-needs support', ['children', 'special_needs'], ['day', 'weekends'], 'Mombasa', 'Nyali', 'female', 31, 7, 14000, 'English, Kiswahili, Arabic', 'Special-needs care course'],
                ['Esther Nekesa', 'Elderly companion, cooking & hygiene', ['elderly', 'house_help'], ['live_in', 'day'], 'Kakamega', 'Kakamega', 'female', 36, 4, 12000, 'English, Kiswahili, Luhya', 'Caregiver certificate'],
                ['Daniel Mutua', 'Night caregiver for bedridden patients', ['bedridden', 'disability', 'elderly'], ['night', 'hourly'], 'Machakos', 'Athi River', 'male', 40, 9, 1500, 'English, Kiswahili, Kamba', 'Home-based care; First Aid'],
            ];
            foreach ($defs as $i => [$name, $headline, $types, $arr, $county, $town, $gender, $age, $yrs, $pay, $langs, $training]) {
                $u = User::create(['name' => $name, 'email' => 'caregiver'.($i + 1).'@demo.test', 'phone' => '2547200000'.($i + 10), 'password' => $pw, 'role' => 'caregiver']);
                CaregiverProfile::create(['user_id' => $u->id, 'headline' => $headline, 'care_types' => $types, 'arrangements' => $arr, 'county' => $county, 'town' => $town,
                    'gender' => $gender, 'age' => $age, 'years_experience' => $yrs, 'expected_pay' => $pay, 'pay_unit' => $pay < 5000 ? 'per_day' : 'per_month',
                    'languages' => $langs, 'training' => $training, 'available' => true,
                    'referee_name' => 'Demo Referee '.($i + 1), 'referee_phone' => '2547990000'.($i + 10), 'referee_relationship' => 'Former employer',
                    'bio' => "Demo profile. I have {$yrs} years of experience in ".strtolower(implode(', ', array_map(fn ($t) => config('noc.care_types')[$t], $types))).'.'])
                    ->forceFill(['verification_status' => 'approved', 'verified_at' => now()->subDays(10),
                        // Esther's certificate is about to expire so the reminder can be seen
                        'good_conduct_issued_on' => ($i === 6 ? now()->subMonths(5)->subDays(10) : now()->subDays(20 + $i * 9))->toDateString()])->save();
                $pay = Payment::create(['user_id' => $u->id, 'plan_id' => $cgPlan->id, 'amount' => $cgPlan->price, 'phone' => $u->phone, 'method' => 'manual',
                    'reference' => 'DEMOCG'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT), 'status' => 'paid', 'paid_at' => now()->subDays(9)]);
                $u->subscriptions()->create(['plan_id' => $cgPlan->id, 'payment_id' => $pay->id, 'starts_at' => now()->subDays(9), 'ends_at' => now()->subDays(9)->addDays($cgPlan->duration_days)]);
                $cgs[] = $u;
            }
            Payment::create(['user_id' => $cgs[4]->id, 'plan_id' => $cgPlan->id, 'amount' => $cgPlan->price, 'phone' => $cgs[4]->phone, 'method' => 'manual', 'reference' => 'DEMOPEND02', 'status' => 'pending']);
            $pending = User::create(['name' => 'Lilian Moraa', 'email' => 'caregiver9@demo.test', 'phone' => '254720000019', 'password' => $pw, 'role' => 'caregiver']);
            CaregiverProfile::create(['user_id' => $pending->id, 'headline' => 'New caregiver, childcare', 'care_types' => ['children'], 'arrangements' => ['day'], 'county' => 'Kisii', 'town' => 'Kisii', 'years_experience' => 2, 'bio' => 'Demo profile awaiting verification.'])
                ->forceFill(['verification_status' => 'pending'])->save();

            $fams = [];
            foreach ([['Wanjiru Kamau', 'Nairobi', 'Lavington'], ['Kiprotich Rotich', 'Uasin Gishu', 'Eldoret'], ['Amina Abdalla', 'Mombasa', 'Nyali']] as $i => [$name, $county, $town]) {
                $u = User::create(['name' => $name, 'email' => 'family'.($i + 1).'@demo.test', 'phone' => '2547300000'.($i + 10), 'password' => $pw, 'role' => 'family']);
                FamilyProfile::create(['user_id' => $u->id, 'county' => $county, 'town' => $town, 'about' => 'Demo family account.']);
                $fams[] = $u;
            }
            [$wanjiru, $kiprotich, $amina] = $fams;

            // Jobs
            $jobs = [
                [$wanjiru, 'Live-in caregiver for my mother with dementia', 'dementia', 'Mother, 81, early dementia, walks with a stick', 'live_in', 'Nairobi', 'Lavington', 25000, 'per_month', 'Help with bathing, meals, medication reminders and short walks. One day off a week. Own room provided.'],
                [$kiprotich, 'Night carer for bedridden father', 'bedridden', 'Father, 76, bedridden after a stroke', 'night', 'Uasin Gishu', 'Eldoret', 1500, 'per_day', 'Turning every 2 hours, diaper changes, feeding support, 7 PM to 7 AM.'],
                [$amina, 'Nanny for twins (18 months)', 'children', 'Twin boys, 18 months', 'day', 'Mombasa', 'Nyali', 16000, 'per_month', 'Monday to Friday, 7 AM to 6 PM. Feeding, play, naps and hygiene.'],
                [$wanjiru, 'Weekend stroke-rehab support', 'rehab', 'Uncle, 68, recovering from a stroke', 'weekends', 'Nairobi', 'Lavington', null, 'negotiable', 'Assist with physiotherapist-prescribed exercises and mobility on Saturdays and Sundays.'],
            ];
            foreach ($jobs as [$fam, $title, $type, $recipient, $arr, $county, $town, $amount, $unit, $desc]) {
                $fam->careJobs()->create(['title' => $title, 'care_type' => $type, 'care_recipient' => $recipient, 'arrangement' => $arr, 'county' => $county, 'town' => $town,
                    'start_date' => now()->addDays(7), 'pay_amount' => $amount, 'pay_unit' => $unit, 'description' => $desc, 'status' => 'open']);
            }
            [$grace, $mercy, $peter, $faith, $janet] = $cgs;
            $wanjiru->careJobs()->first()->applications()->create(['caregiver_id' => $grace->id, 'message' => 'I have 8 years of dementia care experience and can start next week.']);
            $wanjiru->careJobs()->first()->applications()->create(['caregiver_id' => $faith->id, 'message' => 'Experienced live-in caregiver, available immediately.']);
            $kiprotich->careJobs()->first()->applications()->create(['caregiver_id' => $peter->id, 'message' => 'I do night shifts for bedridden patients and live in Eldoret.']);

            CareRequest::create(['family_id' => $kiprotich->id, 'caregiver_id' => $janet->id, 'care_type' => 'bedridden', 'arrangement' => 'night', 'message' => 'Demo request: need a night carer for my father who is bedridden.', 'status' => 'pending']);
            CareRequest::create(['family_id' => $amina->id, 'caregiver_id' => $mercy->id, 'care_type' => 'children', 'arrangement' => 'day', 'message' => 'Demo request: nanny for twins in Nyali, weekdays.', 'status' => 'accepted']);

            // Wanjiru unlocked Grace, Grace accepted her request, and Wanjiru reviewed her
            CareRequest::create(['family_id' => $wanjiru->id, 'caregiver_id' => $grace->id, 'care_type' => 'dementia', 'arrangement' => 'live_in', 'message' => 'Demo request: live-in care for my mother with dementia.', 'status' => 'accepted']);
            Unlock::create(['family_id' => $wanjiru->id, 'caregiver_id' => $grace->id]);
            Review::create(['family_id' => $wanjiru->id, 'caregiver_id' => $grace->id, 'stars' => 5, 'comment' => 'Patient and kind with my mother. Highly recommended.']);
            $grace->caregiverProfile->forceFill(['rating_avg' => 5, 'rating_count' => 1])->save();

            // Skills and badges
            [, , , , , , $esther, $daniel] = $cgs;
            $skills = [
                [$grace, 'dementia_care', 'tested'], [$grace, 'elderly_care', 'tested'], [$grace, 'medication_support', 'declared'],
                [$mercy, 'baby_child_care', 'tested'], [$mercy, 'first_aid', 'certified'],
                [$peter, 'bedridden_care', 'tested'], [$peter, 'disability_support', 'tested'], [$peter, 'first_aid', 'certified'],
                [$faith, 'elderly_care', 'tested'], [$faith, 'infection_control', 'declared'],
                [$janet, 'bedridden_care', 'certified'], [$janet, 'medication_support', 'tested'],
                [$esther, 'elderly_care', 'declared'],
                [$daniel, 'bedridden_care', 'tested'], [$daniel, 'first_aid', 'pending'],
            ];
            Storage::disk('local')->put('documents/demo-certificate.pdf', "%PDF-1.1\n% Demo certificate placeholder\n");
            foreach ($skills as [$u, $skill, $level]) {
                $row = CaregiverSkill::create(['user_id' => $u->id, 'skill' => $skill, 'declared' => true]);
                if ($level === 'tested') {
                    $row->forceFill(['quiz_passed_at' => now()->subDays(5), 'quiz_score' => 9])->save();
                } elseif (in_array($level, ['certified', 'pending'], true)) {
                    $doc = $u->documents()->create(['type' => 'skill_cert', 'path' => 'documents/demo-certificate.pdf', 'original_name' => 'demo-'.$skill.'-certificate.pdf']);
                    $row->forceFill(['cert_status' => $level === 'certified' ? 'approved' : 'pending', 'certificate_document_id' => $doc->id,
                        'certified_at' => $level === 'certified' ? now()->subDays(4) : null])->save();
                }
            }

            // College qualifications
            $quals = [
                [$grace, 'Caregiving', 'Nairobi Caregivers Training Institute', 'certificate', 2019, 'approved'],
                [$peter, 'Certified Nursing Assistant', 'Eldoret Health Training College', 'certificate', 2020, 'approved'],
                [$janet, 'Palliative care', 'Hospice training centre', 'short_course', 2021, 'unverified'],
                [$mercy, 'Early Childhood Development Education', 'South B Teachers College', 'diploma', 2018, 'pending'],
            ];
            foreach ($quals as [$u, $course, $college, $level, $year, $status]) {
                $doc = $status === 'unverified' ? null : $u->documents()->create(['type' => 'certificate', 'path' => 'documents/demo-certificate.pdf', 'original_name' => 'demo-college-certificate.pdf']);
                \App\Models\Qualification::create(['user_id' => $u->id, 'course' => $course, 'institution' => $college, 'level' => $level, 'year' => $year, 'document_id' => $doc?->id])
                    ->forceFill(['status' => $status, 'checked_at' => $status === 'approved' ? now()->subDays(3) : null])->save();
            }

            User::where('email', 'like', '%@demo.test')->get()->each(fn ($u) => Consent::grantAll($u));
        });

        $this->info('Demo data created. All demo accounts use password: '.$pw);
        $this->table(['Login', 'Who'], [
            ['family1@demo.test', 'Wanjiru (Nairobi): 2 jobs with applicants, hired-by-request & reviewed Grace'],
            ['family2@demo.test', 'Kiprotich (Eldoret): night-carer job, request to Janet'],
            ['family3@demo.test', 'Amina (Mombasa): nanny job, Mercy accepted her request'],
            ['caregiver1@demo.test', 'Grace Njeri, elderly & dementia (Nairobi): 2 tested badges'],
            ['caregiver7@demo.test', 'Esther Nekesa: Good Conduct about to expire'],
            ['caregiver8@demo.test', 'Daniel Mutua: First Aid certificate waiting for admin'],
            ['caregiver3@demo.test', 'Peter Kiprono, rehab (Eldoret)'],
            ['caregiver5@demo.test', 'Janet Chepkemoi, palliative (Eldoret): pending request, renewal payment awaiting admin'],
            ['caregiver9@demo.test', 'Lilian Moraa: awaiting verification'],
        ]);
        return self::SUCCESS;
    }

    private function purge(): void
    {
        $ids = User::where('email', 'like', '%@demo.test')->pluck('id');
        if ($ids->isEmpty()) {
            return;
        }
        DB::transaction(function () use ($ids) {
            DB::table('notifications')->where('notifiable_type', User::class)->whereIn('notifiable_id', $ids)->delete();
            User::whereIn('id', $ids)->delete(); // everything else cascades
        });
    }
}
