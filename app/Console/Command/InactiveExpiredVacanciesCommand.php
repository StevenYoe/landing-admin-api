<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vacancy;
use Illuminate\Support\Carbon;

class InactiveExpiredVacanciesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vacancies:inactive-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set vacancies with passed closed date to inactive automatically';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::now()->startOfDay();
        
        // Get active vacancies with closed date before today
        $expiredVacancies = Vacancy::where('v_is_active', true)
            ->whereNotNull('v_closed_date')
            ->where('v_closed_date', '<', $today)
            ->get();
        
        $count = 0;
        
        foreach ($expiredVacancies as $vacancy) {
            // Update the vacancy to inactive and set updated_by to "System"
            $vacancy->update([
                'v_is_active' => false,
                'v_updated_by' => 'System'
            ]);
            
            $count++;
        }
        
        $this->info("Successfully inactivated {$count} expired vacancies.");
        
        return Command::SUCCESS;
    }
}