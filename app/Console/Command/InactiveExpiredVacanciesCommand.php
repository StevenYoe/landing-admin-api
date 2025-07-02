<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vacancy;
use Illuminate\Support\Carbon;

// This command automatically sets expired vacancies to inactive status
class InactiveExpiredVacanciesCommand extends Command
{
    /**
     * The name and signature of the console command.
     * Used to call this command via artisan CLI.
     *
     * @var string
     */
    protected $signature = 'vacancies:inactive-expired';

    /**
     * The console command description.
     * Shown in the list of artisan commands.
     *
     * @var string
     */
    protected $description = 'Set vacancies with passed closed date to inactive automatically';

    /**
     * Execute the console command.
     * This method finds all active vacancies whose closed date has passed and sets them to inactive.
     * It also updates the 'v_updated_by' field to 'System'.
     *
     * @return int
     */
    public function handle()
    {
        // Get today's date at the start of the day
        $today = Carbon::now()->startOfDay();
        
        // Get all active vacancies with a closed date before today
        $expiredVacancies = Vacancy::where('v_is_active', true)
            ->whereNotNull('v_closed_date')
            ->where('v_closed_date', '<', $today)
            ->get();
        
        $count = 0;
        
        // Loop through each expired vacancy and set it to inactive
        foreach ($expiredVacancies as $vacancy) {
            // Update the vacancy to inactive and set updated_by to "System"
            $vacancy->update([
                'v_is_active' => false,
                'v_updated_by' => 'System'
            ]);
            
            $count++;
        }
        
        // Output the result to the console
        $this->info("Successfully inactivated {$count} expired vacancies.");
        
        return Command::SUCCESS;
    }
}