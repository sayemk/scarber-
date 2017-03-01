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
        $minLimit = 0

        $dom = new Dom();
        $dom->setOptions([
            'cleanupInput' => true, // Set a global option to enable strict html parsing.
        ]);

        while ($minLimit<$maxLimit)
        {
            $dom->loadFromUrl($basisUrl.$minLimit);
            $htmls = $dom->find('.bodytext');


            foreach ($htmls as $html)
            {
                foreach ($html->find('a') as $company)
                {

                    $this->info($company->text);
                    $this->info($company->href);
                }


                $this->info('');


            }

            $minLimit+=20;
        }
    }
}
