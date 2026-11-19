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
    }
}
