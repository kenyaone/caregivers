<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Placeholder prices: edit them in Admin → Plans.
        // Caregivers: minimum 3 months upfront, or a full year.
        Plan::firstOrCreate(['audience' => 'caregiver', 'name' => 'Caregiver 3 Months'],
            ['price' => 300, 'duration_days' => 90, 'description' => 'Be listed for families and apply for unlimited care jobs for 3 months']);
        Plan::firstOrCreate(['audience' => 'caregiver', 'name' => 'Caregiver Annual'],
            ['price' => 1200, 'duration_days' => 365, 'description' => 'Be listed and apply for unlimited care jobs for 12 months (best value)']);
        Plan::firstOrCreate(['audience' => 'family', 'name' => 'Contact Pass'],
            ['price' => 500, 'duration_days' => 30, 'credits' => 3, 'description' => 'Unlock the phone numbers of 3 caregivers within 30 days']);
        Plan::firstOrCreate(['audience' => 'family', 'name' => 'Family Plus'],
            ['price' => 1200, 'duration_days' => 60, 'credits' => 10, 'description' => 'Unlock 10 caregiver contacts within 60 days']);
    }
}