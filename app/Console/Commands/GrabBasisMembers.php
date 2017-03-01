<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PHPHtmlParser\Dom;

class GrabBasisMembers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scarb:basis-members';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scarb basis members from website';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $basisUrl = 'http://www.basis.org.bd/index.php/members_area/member_list/';

        $maxLimit = 1040;
        $minLimit = 0;

        $dom = new Dom();
        $dom->setOptions([
            'cleanupInput' => true, // Set a global option to enable strict html parsing.
        ]);


        $counter = 0;
        while ($minLimit<$maxLimit)
        {
            $dom->loadFromUrl($basisUrl.$minLimit);
            $htmls = $dom->find('.bodytext');


            foreach ($htmls as $html)
            {
                $companies = $html->find('a');
                $completedCompany = '';

                foreach ($companies as $company)
                {

                    $validLink = $company->find('b');
                    if(count($validLink))
                    {



                        $counter++;
                        $this->info('Completd - '.$validLink->text);


                    }



                }

                $this->info($counter);



            }

            $minLimit+=20;
        }
    }
}
