<?php
 
namespace App\Console\Commands;
 
use App\Support\Services\FIEXMLService;
use Illuminate\Console\Command;
 
class ParseXMLFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evf:parse {path : the file path}';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse the provided file';
 
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $path = $this->argument('path');
        $xml = new FIEXMLService($path);
        $xml->handle();
    }
}
