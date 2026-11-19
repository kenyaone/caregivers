<?php

// Config for CareGivers254 (caregivers.afyayako.co.ke). Kept under the "noc" key so shared code works unchanged.
return [
    'brand' => env('APP_NAME', 'CareGivers254'),
    'support_phone' => env('NOC_SUPPORT_PHONE', ''),
    'support_email' => env('NOC_SUPPORT_EMAIL', ''),
    'mpesa_till' => env('NOC_MPESA_TILL', ''),
    'mpesa_account_name' => env('NOC_MPESA_ACCOUNT_NAME', 'CareGivers254'),

    'care_types' => [
        'elderly' => 'Elderly care',
        'dementia' => 'Dementia & memory care',
        'bedridden' => 'Bedridden / post-hospital care',
        'palliative' => 'Palliative & end-of-life care',
        'children' => 'Nanny / childcare',
        'special_needs' => 'Special-needs children',
        'live_in' => 'Live-in caregiver',
        'house_help' => 'House help with care duties',
        'disability' => 'Disability & mobility support',
        'rehab' => 'Stroke / rehab support',
    ],

    'arrangements' => [
        'live_in' => 'Live-in',
        'day' => 'Day (live-out)',
        'night' => 'Night',
        'hourly' => 'Hourly / part-time',
        'weekends' => 'Weekends',
    ],

    'pay_units' => [
        'per_month' => 'per month',
        'per_day' => 'per day',
        'per_hour' => 'per hour',
        'negotiable' => 'negotiable',
    ],

    'document_types' => [
        'caregiver' => [
            'national_id' => 'National ID (front & back)',
            'good_conduct' => 'Police Certificate of Good Conduct',
            'certificate' => 'Caregiver / CNA / training certificate',
            'reference_letter' => 'Reference letter',
            'cv' => 'CV',
            'other' => 'Other',
        ],
        'family' => [
            'national_id' => 'National ID',
            'other' => 'Other',
        ],
    ],

    'counties' => [
        'Baringo', 'Bomet', 'Bungoma', 'Busia', 'Elgeyo-Marakwet', 'Embu', 'Garissa', 'Homa Bay',
        'Isiolo', 'Kajiado', 'Kakamega', 'Kericho', 'Kiambu', 'Kilifi', 'Kirinyaga', 'Kisii',
        'Kisumu', 'Kitui', 'Kwale', 'Laikipia', 'Lamu', 'Machakos', 'Makueni', 'Mandera',
        'Marsabit', 'Meru', 'Migori', 'Mombasa', "Murang'a", 'Nairobi', 'Nakuru', 'Nandi',
        'Narok', 'Nyamira', 'Nyandarua', 'Nyeri', 'Samburu', 'Siaya', 'Taita-Taveta', 'Tana River',
        'Tharaka-Nithi', 'Trans Nzoia', 'Turkana', 'Uasin Gishu', 'Vihiga', 'Wajir', 'West Pokot',
    ],

    // Bump this date whenever the wording below changes: everyone is asked to agree again.
    'consent_version' => '2026-11-19',
    'consents' => [
        'terms' => 'I have read and agree to the CareGivers254 Terms of Use and Privacy Notice.',
        'data' => 'I consent to CareGivers254 processing my personal data and uploaded documents to verify my account and run the service, including :share, as described in the Privacy Notice (Data Protection Act, 2019).',
        'caregiver_declaration' => 'I declare that my national ID, Certificate of Good Conduct, certificates and references are genuine, current and my own, and that I have never been convicted of an offence involving violence, abuse, neglect or theft. I will never harm, neglect or exploit anyone in my care, and I authorise CareGivers254 to verify my details, contact my referees and report false documents or abuse to the authorities.',
        'caregiver_independent' => 'I understand that CareGivers254 is a marketplace, not my employer or agent. Any job is an agreement between me and the family, and I will agree pay, hours, days off and duties with them directly.',
        'family_declaration' => 'I declare that the information I give is true and that I will interview the caregiver and check their references myself. I will treat the caregiver with dignity, pay the agreed wage on time, give a safe place to sleep and rest if live-in, and respect Kenyan employment law. I accept that CareGivers254 is a marketplace, not an employment agency, and is not responsible for the care given or for payments between us.',
        'job_post' => 'I confirm that this job is genuine, the details are accurate, and the pay and conditions offered are lawful.',
    ],

    'verify_links' => [
        'good_conduct' => 'https://www.ecitizen.go.ke',
    ],

    // Checks an admin must tick before approving an account.
    'verification_checks' => [
        'caregiver' => [
            'id' => 'National ID is clear, genuine-looking, and the name and photo match the profile',
            'good_conduct' => 'Certificate of Good Conduct is in the caregiver\'s name, shows no record, was issued within the last 12 months, and its serial/QR code checks out',
            'references' => 'At least one referee was phoned and confirmed the caregiver\'s work and character',
            'interview' => 'Caregiver was phoned and confirmed their experience and the care types they listed',
        ],
        'family' => [
            'id' => 'National ID is clear and matches the account name',
            'contact' => 'Family member was phoned and the request looks genuine',
        ],
    ],

    'kopokopo' => [
        'enabled' => (bool) env('KOPOKOPO_CLIENT_ID'),
        'base_url' => rtrim(env('KOPOKOPO_BASE_URL', 'https://sandbox.kopokopo.com'), '/'),
        'client_id' => env('KOPOKOPO_CLIENT_ID'),
        'client_secret' => env('KOPOKOPO_CLIENT_SECRET'),
        'api_key' => env('KOPOKOPO_API_KEY'),
        'till_number' => env('KOPOKOPO_TILL_NUMBER'),
    ],

    'sms' => [
        'enabled' => (bool) env('AT_API_KEY'),
        'username' => env('AT_USERNAME', 'sandbox'),
        'api_key' => env('AT_API_KEY'),
        'sender_id' => env('AT_SENDER_ID'),
    ],
];
