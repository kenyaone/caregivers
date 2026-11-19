@extends('layouts.app', ['title' => 'Find a trusted caregiver in Kenya', 'description' => 'Find verified, background-checked caregivers for elderly care, children, live-in care and disability support across Kenya. Caregivers: find care jobs near you.'])
@section('content')
<style>
.cg-hero{position:relative;overflow:hidden;color:#fff;background:radial-gradient(1000px 500px at 90% -10%,#14b8a6 0%,transparent 55%),radial-gradient(800px 500px at -10% 110%,#be185d55 0%,transparent 55%),linear-gradient(135deg,#042f2e,#0f4c4a 55%,#115e59)}
.cg-wrap{position:relative;max-width:72rem;margin:0 auto;padding:4.5rem 1rem 5rem;display:grid;gap:3rem;align-items:center}
@media(min-width:900px){.cg-wrap{grid-template-columns:1.1fr .9fr;padding:6rem 1rem}}
.cg-h1{font-size:clamp(2.3rem,5.2vw,3.8rem);line-height:1.05;font-weight:900;letter-spacing:-.03em}
.cg-grad{background:linear-gradient(90deg,#fde68a,#fbbf24 45%,#f9a8d4);-webkit-background-clip:text;background-clip:text;color:transparent}
.cg-lead{margin-top:1.25rem;font-size:1.12rem;line-height:1.7;color:#ccfbf1;max-width:34rem}
.cg-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.9rem 1.4rem;border-radius:.85rem;font-weight:800;font-size:.95rem;transition:transform .15s}
.cg-btn:hover{transform:translateY(-2px)}
.cg-gold{background:#fbbf24;color:#1c1917;box-shadow:0 10px 30px -10px rgba(251,191,36,.8)}
.cg-glass{background:rgba(255,255,255,.08);color:#fff;border:1px solid rgba(255,255,255,.3)}
.cg-teal{background:#0f766e;color:#fff}
.cg-trust{margin-top:2rem;display:flex;flex-wrap:wrap;gap:.6rem 1.4rem;font-size:.9rem;color:#a7f3d0}
.cg-trust span:before{content:"✓ ";color:#5eead4;font-weight:900}
.cg-photo{position:relative;height:440px}
.cg-photo img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center 35%;border-radius:2rem;border:4px solid rgba(255,255,255,.12);box-shadow:0 40px 80px -30px rgba(0,0,0,.7)}
.cg-badge{position:absolute;left:-14px;bottom:28px;background:#fff;color:#0f172a;border-radius:1rem;padding:.8rem 1rem;box-shadow:0 25px 50px -20px rgba(0,0,0,.6);font-size:.85rem;max-width:260px}
.cg-badge b{display:block;font-size:.95rem}
@media(max-width:899px){.cg-photo{height:300px}.cg-badge{left:12px}}
.cg-sec{max-width:72rem;margin:0 auto;padding:4.5rem 1rem}
.cg-eyebrow{font-size:.78rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#0f766e}
.cg-h2{font-size:clamp(1.7rem,3.4vw,2.5rem);font-weight:900;letter-spacing:-.02em;line-height:1.15;color:#0f172a;margin-top:.5rem}
.cg-types{display:grid;gap:1rem;margin-top:2.5rem;grid-template-columns:repeat(2,1fr)}
@media(min-width:900px){.cg-types{grid-template-columns:repeat(4,1fr)}}
.cg-type{display:block;border-radius:1.25rem;padding:1.4rem;background:#fff;border:1px solid #e2e8f0;transition:transform .2s,border-color .2s}
.cg-type:hover{transform:translateY(-3px);border-color:#5eead4}
.cg-type .i{font-size:1.8rem}
.cg-type b{display:block;margin-top:.5rem;color:#0f172a}
.cg-type span{display:block;font-size:.85rem;color:#64748b;margin-top:.2rem}
.cg-duo{display:grid;gap:1.5rem;margin-top:2.5rem}
@media(min-width:820px){.cg-duo{grid-template-columns:1fr 1fr}}
.cg-panel{border-radius:1.5rem;padding:2rem;background:#fff;border:1px solid #e2e8f0}
.cg-panel.dark{background:linear-gradient(160deg,#0f172a,#134e4a);color:#e2e8f0;border-color:#0f172a}
.cg-panel h3{font-size:1.4rem;font-weight:900}
.cg-panel.dark h3{color:#fff}
.cg-panel ol{margin-top:1rem;display:grid;gap:.75rem;counter-reset:s}
.cg-panel li{counter-increment:s;display:flex;gap:.75rem;line-height:1.5}
.cg-panel li:before{content:counter(s);flex:none;display:grid;place-items:center;width:1.8rem;height:1.8rem;border-radius:.6rem;background:#ccfbf1;color:#0f766e;font-weight:900;font-size:.85rem}
.cg-panel.dark li:before{background:rgba(94,234,212,.15);color:#5eead4}
.cg-safe{border-radius:1.5rem;padding:2rem;background:#fff7ed;border:1px solid #fed7aa;display:grid;gap:1.25rem}
@media(min-width:820px){.cg-safe{grid-template-columns:repeat(3,1fr)}}
.cg-safe b{display:block;color:#7c2d12}
.cg-safe p{font-size:.9rem;color:#78350f;margin-top:.25rem;line-height:1.5}
</style>

<section class="cg-hero">
    <div class="cg-wrap">
        <div>
            <span class="badge" style="background:rgba(255,255,255,.12);color:#fff">Background-checked caregivers · All 47 counties</span>
            <h1 class="cg-h1" style="margin-top:1.1rem">Trusted care for the people<br><span class="cg-grad">you love most.</span></h1>
            <p class="cg-lead">Find a verified caregiver for an elderly parent, a newborn, a loved one recovering from a stroke, or someone living with a disability. Every caregiver's ID and police Certificate of Good Conduct is checked before they're listed.</p>
            <div style="margin-top:2rem;display:flex;flex-wrap:wrap;gap:.75rem">
                <a href="{{ route('caregivers.index') }}" class="cg-btn cg-gold">Find a caregiver →</a>
                <a href="{{ route('register', ['as' => 'caregiver']) }}" class="cg-btn cg-glass">I'm a caregiver, find work</a>
                <button type="button" data-cg-install class="cgi-only cg-btn cg-glass" style="cursor:pointer"><span aria-hidden="true">&#128242;</span> Install the app</button>
            </div>
            <div class="cg-trust"><span>ID & Good Conduct checked</span><span>References called</span><span>Families rate every caregiver</span></div>
        </div>
        <div class="cg-photo" aria-hidden="true">
            <img src="{{ asset('img/nurse-10.jpg') }}" alt="">
            <div class="cg-badge"><b>✓ Verified caregiver</b>National ID, Good Conduct certificate and referee confirmed by our team.</div>
        </div>
    </div>
</section>

{{-- Get the app --}}
<section class="cgi-only cgi-block" style="background:#fffbeb;border-bottom:1px solid #fde68a">
    <div class="cg-sec" style="padding-top:3.5rem;padding-bottom:3.5rem;display:grid;gap:2rem;align-items:center" id="get-app">
        <div style="display:flex;gap:1.5rem;align-items:center;flex-wrap:wrap">
            <img src="/icons/icon-192.png" alt="CareGivers254 app icon" style="width:96px;height:96px;border-radius:24px;box-shadow:0 20px 40px -15px rgba(15,118,110,.6)">
            <div style="flex:1;min-width:16rem">
                <div class="cg-eyebrow" style="color:#b45309">Free app</div>
                <h2 class="cg-h2" style="margin-top:.25rem">Put CareGivers254 on your phone</h2>
                <p style="margin-top:.6rem;color:#57534e;line-height:1.6;max-width:36rem">Install it in seconds, no Play Store or App Store needed and under 1&nbsp;MB. Caregivers get job and request alerts the moment they come in; families can reach their caregivers in one tap.</p>
                <div style="margin-top:1.25rem;display:flex;flex-wrap:wrap;gap:.75rem;align-items:center">
                    <button type="button" data-cg-install class="cg-btn cg-gold cgi-pulse" style="cursor:pointer;border:0;font-size:1.05rem;padding:1rem 1.6rem"><span aria-hidden="true">&#128242;</span> Install the app</button>
                    <span style="font-size:.85rem;color:#78716c">Android, iPhone and computers</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cg-sec">
    <div class="cg-eyebrow">What care do you need?</div>
    <h2 class="cg-h2">Caregivers for every stage of life</h2>
    <div class="cg-types">
        @foreach([['elderly', '👵', 'Elderly care', 'Companionship, meals, hygiene, medication reminders'], ['bedridden', '🛏️', 'Bedridden & post-hospital', 'Turning, feeding, bed baths, wound-care support'], ['children', '👶', 'Nannies & childcare', 'Newborns, toddlers and school-going children'], ['special_needs', '🧩', 'Special-needs children', 'Autism, cerebral palsy, Down syndrome support'], ['dementia', '🧠', 'Dementia & memory care', 'Patient, structured care for memory loss'], ['live_in', '🏠', 'Live-in caregivers', 'Round-the-clock care at home'], ['disability', '♿', 'Disability support', 'Mobility, transfers and daily living'], ['rehab', '💪', 'Stroke & rehab', 'Exercises and recovery support at home']] as [$key, $icon, $title, $text])
            <a href="{{ route('caregivers.index', ['care_type' => $key]) }}" class="cg-type"><div class="i">{{ $icon }}</div><b>{{ $title }}</b><span>{{ $text }}</span></a>
        @endforeach
    </div>
</section>

@if($featured->count())
<section class="cg-sec" style="padding-top:0">
    <div style="display:flex;flex-wrap:wrap;align-items:end;justify-content:space-between;gap:1rem">
        <div><div class="cg-eyebrow">Available now</div><h2 class="cg-h2">Verified caregivers</h2></div>
        <a href="{{ route('caregivers.index') }}" class="link">See all caregivers →</a>
    </div>
    <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @foreach($featured as $p)<x-caregiver-card :profile="$p"/>@endforeach
    </div>
</section>
@endif

<section style="background:linear-gradient(180deg,#f0fdfa,#f8fafc)">
    <div class="cg-sec">
        <div class="cg-eyebrow">How it works</div>
        <h2 class="cg-h2">Two ways to find the right caregiver</h2>
        <div class="cg-duo">
            <div class="cg-panel">
                <h3>For families</h3>
                <ol>
                    <li><span><b>Browse verified caregivers</b> by care type, county and live-in or day work. You'll see photos, experience, expected pay and reviews.</span></li>
                    <li><span><b>Or post a care job for free.</b> Matching caregivers near you are alerted and apply.</span></li>
                    <li><span><b>Unlock contacts</b> with a contact pass{{ $familyPlan ? ' (from KES '.number_format($familyPlan->price).')' : '' }}. Call, interview and check references.</span></li>
                    <li><span><b>Agree terms directly</b> with your caregiver, then leave a review to help other families.</span></li>
                </ol>
                <a href="{{ route('register') }}" class="cg-btn cg-teal" style="margin-top:1.5rem">Create a family account</a>
            </div>
            <div class="cg-panel dark">
                <h3>For caregivers</h3>
                <ol>
                    <li><span><b>Create your profile</b> with a photo, your skills, availability and expected pay.</span></li>
                    <li><span><b>Get verified.</b> Upload your ID and Certificate of Good Conduct; we call your referee.</span></li>
                    <li><span><b>Subscribe{{ $caregiverPlan ? ' (KES '.number_format($caregiverPlan->price).' a year)' : '' }}</b> to be listed for families and apply for unlimited jobs.</span></li>
                    <li><span><b>Get care requests</b> from families near you. Keep 100% of what you earn.</span></li>
                </ol>
                <a href="{{ route('register', ['as' => 'caregiver']) }}" class="cg-btn cg-gold" style="margin-top:1.5rem">Join as a caregiver</a>
            </div>
        </div>
    </div>
</section>

@if($jobs->count())
<section class="cg-sec">
    <div style="display:flex;flex-wrap:wrap;align-items:end;justify-content:space-between;gap:1rem">
        <div><div class="cg-eyebrow">For caregivers</div><h2 class="cg-h2">Latest care jobs</h2></div>
        <a href="{{ route('jobs.index') }}" class="link">All jobs →</a>
    </div>
    <div class="mt-6 grid gap-4 md:grid-cols-2">@foreach($jobs as $job)<x-job-card :job="$job"/>@endforeach</div>
</section>
@endif

<section class="cg-sec" style="padding-top:{{ $jobs->count() ? '0' : '4.5rem' }}">
    <div class="cg-eyebrow" style="color:#c2410c">Safety first</div>
    <h2 class="cg-h2">You're inviting someone into your home. We take that seriously.</h2>
    <div class="cg-safe" style="margin-top:2rem">
        <div><b>🪪 Checked before listing</b><p>Every caregiver's national ID and police Certificate of Good Conduct is reviewed, and a referee is phoned, before the profile goes live.</p></div>
        <div><b>⭐ Honest reviews</b><p>Only families who unlocked a caregiver can review them, so ratings come from real contact.</p></div>
        <div><b>🤝 Meet first, always</b><p>Interview in person, call the references yourself, and agree pay, duties and days off in writing before the first day.</p></div>
    </div>
</section>
@endsection
