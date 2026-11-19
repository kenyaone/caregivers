@extends('layouts.app', ['title' => 'Terms of Use & Privacy Notice'])
@section('content')
<div class="max-w-3xl mx-auto px-4 py-12">
    <div class="card space-y-5 text-slate-700 text-sm leading-relaxed">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Terms of Use</h1>
            <p class="text-xs text-slate-500 mt-1">Version {{ config('noc.consent_version') }}. Draft: have a Kenyan advocate review these terms before launch.</p>
        </div>

        <h2 class="font-bold text-slate-900">1. What CareGivers254 is</h2>
        <p>CareGivers254 is an online marketplace where families in Kenya find caregivers, and caregivers find care work. We are <b>not</b> an employer, employment agency or healthcare provider. Any engagement is an agreement directly between the family and the caregiver. We do not supervise care and we are not a party to that agreement.</p>

        <h2 class="font-bold text-slate-900">2. Caregivers</h2>
        <ul class="list-disc pl-5 space-y-1">
            <li>Your national ID, police Certificate of Good Conduct, certificates and references must be genuine, current and your own. Forged or borrowed documents lead to permanent removal and may be reported to the police.</li>
            <li>You must never harm, neglect, abuse or exploit anyone in your care, or take anything that is not yours. We report suspected abuse to the authorities.</li>
            <li>Only offer care you are trained and able to give. Nursing and medical procedures must be left to licensed professionals.</li>
            <li>You are responsible for agreeing pay, hours, days off and duties with the family, and for your own conduct and statutory obligations.</li>
        </ul>

        <h2 class="font-bold text-slate-900">3. Families</h2>
        <ul class="list-disc pl-5 space-y-1">
            <li>Information you post must be true, and jobs must be genuine and lawful.</li>
            <li>Before engaging a caregiver, you must interview them in person, check their original ID and Certificate of Good Conduct, and call their references yourself.</li>
            <li>Treat caregivers with dignity. Pay the agreed wage on time, respect the agreed hours and days off, give a safe place to sleep and rest if live-in, and follow Kenyan employment law, including minimum wage rules where they apply.</li>
            <li>You remain responsible for supervising the care of your family member.</li>
        </ul>

        <h2 class="font-bold text-slate-900">4. Verification and its limits</h2>
        <p>We review the documents caregivers upload and phone at least one referee before listing them. Verification reduces risk but does not guarantee anyone's conduct or competence, and documents can be forged. Families must make their own checks before and during any engagement.</p>

        <h2 class="font-bold text-slate-900">5. Payments and fees</h2>
        <p>Caregivers pay a subscription to be listed and to apply for jobs. Using the platform is free for families. Subscription fees are non-refundable once a subscription is active, unless the law requires otherwise. We take no commission from caregivers' pay.</p>

        <h2 class="font-bold text-slate-900">6. Reviews, complaints and suspension</h2>
        <p>Only families whose care request a caregiver accepted, or who hired the caregiver through a job, can review them, and reviews must be honest. We may suspend or remove any account for false information, abuse, harassment, non-payment of wages or unsafe behaviour, and we report serious matters to the police. To report a safety concern, contact us immediately.</p>

        <h2 class="font-bold text-slate-900">7. Liability</h2>
        <p>To the extent the law allows, CareGivers254 is not liable for injuries, losses, theft, unpaid wages or other harm arising from an engagement between a family and a caregiver. Each user agrees to compensate CareGivers254 for claims that result from their own false information, misconduct or breach of these terms.</p>

        <h2 id="privacy" class="text-xl font-bold text-slate-900 pt-4 border-t">Privacy Notice</h2>
        <p>We process personal data under the Kenya Data Protection Act, 2019.</p>
        <ul class="list-disc pl-5 space-y-1">
            <li><b>What we collect:</b> name, phone, email, location, photo, skills and experience, uploaded documents, jobs, requests, reviews, payment references, and a record of what you agreed to (time, IP address, device).</li>
            <li><b>Who sees it:</b> anyone browsing sees a caregiver's photo, first name and last initial, skills, area, expected pay and reviews. A caregiver's full name, phone, email and documents (except the ID card and selfie) are shown only to signed-in families who choose to view them, and the caregiver is told who viewed their number. When a family views a caregiver's number or sends a request, the caregiver sees the family's name, area and phone number. Referee details are seen only by our verification team. Our verification team sees everything. We share data with the police or regulators only when the law requires it, or to report suspected abuse or fraud.</li>
            <li><b>Your rights:</b> you can ask to see, correct or delete your data, or withdraw consent, by contacting us. If you withdraw consent, you can no longer use the platform. We may keep verification, payment and safety records for as long as the law requires.</li>
        </ul>
    </div>
</div>
@endsection
