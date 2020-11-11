<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class CheckCommand extends Command
{
    protected $validChars = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'check {length=2} {ext=.pl}';

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
        $time = time();
        $domains = collect();
        /**
         * Define domain array
         */
        foreach ($this->validChars as $validChar) {
            $domains->put('a' . $validChar . $this->argument('ext') . '.', ['exists' => false]);
        }
        $this->line('Created domains array. Took: ' . time()-$time . ' seconds.');
        $time = time();
        /**
         * Check domains via dig
         */
        exec('dig +noall +answer @1.1.1.1 ' . implode(' ', $domains->keys()->toArray()) . ' A', $outputArray);
        foreach ($outputArray as $outputLine) {
            $parts = preg_split('/\s+/', $outputLine);
            $domains[$parts[0]] = [
                'domain' => $parts[0],
                'ttl' => $parts[1],
                'record' => $parts[3],
                'ip' => $parts[4],
                'exists' => true
            ];
        }
        $this->line('Checked domains information on dig. Took: ' . time() - $time . ' seconds.');
        $time = time();
        /**
         * Check domains that has no information on dig via host
         */
        foreach ($domains->where('exists', false)->all() as $domainKey => $domainValue) {
            $outputHost = null;
            exec('host -t a ' . $domainKey, $outputHost);
            if (is_numeric(stripos($outputHost[0], 'NXDOMAIN'))) {
                $domains[$domainKey] = [
                    'domain' => $domainKey,
                    'exists' => false
                ];
            } else {
                $domains[$domainKey] = [
                    'domain' => $domainKey,
                    'exists' => true
                ];
            }
        }

        $this->line('Checked domains information on host. Took: ' . time()-$time . 'seconds.');
        $time = time();
        /**
         * Print domains that are not registered
         */
        $count = 0;
        $count += $domains->where('exists', false)->count();
        $domainNames = $domains->where('exists', false)->keys()->toArray();
        $this->info('Finished! Found: ' . $count . ' ' . implode(' ', $domainNames));
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
