<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class CheckCommand extends Command
{
    protected $validChars = ['a' , 'b', 'c', 'd', 'e' , 'f', 'g', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'check {long=2} {ext=.pl}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Check domain entries for domains';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    
        foreach($this->validChars as $ch1){
            $this->info('Checking ' . $ch1 . 'x.pl');
            foreach($this->validChars as $ch2){
                foreach($this->validChars as $ch3){
                    $output = null;
                    exec('host -t a ' . $ch1 .   $ch2 . $ch3  . '.pl', $output);
                    if(is_numeric(stripos($output[0], 'not found'))){
                        $this->error($output[0] . 'go to check it: ' . 'https://dns.pl/whois?domainName=' . $ch1 .  $ch2 .  $ch3  . '.pl');
                    }else{
                        $this->line($output[0]);
                    }
                }
            }
        }
        $this->info('Finished!');
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
