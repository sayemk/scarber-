<?php

namespace App\Console\Commands;

use App\Member;
use Illuminate\Console\Command;
use League\Flysystem\Exception;
use PHPHtmlParser\Dom;

class GrabBasisMembers extends Command
{

    protected $basis_id;
    protected $c_member_id;
    protected $c_name;
    protected $c_establishment;
    protected $c_address;
    protected $c_contact;
    protected $c_email;
    protected $c_website;
    protected $cc_name;
    protected $cc_designation;
    protected $cc_mobile;
    protected $cc_email;


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
        $neededInfo = [
                        'Company Name','Year of establishment','Address','Contact No.','E-mail','Company website',
                        'Name','Designation','Mobile','BASIS Membership No.',
                ];

        $dom = new Dom();
        $dom->setOptions([
            'cleanupInput' => true, // Set a global option to enable strict html parsing.
        ]);


        $counter = $minLimit;
        while ($minLimit<=$maxLimit)
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
                        $profile = new Dom();
                        $profile->setOptions([
                            'cleanupInput' => true, // Set a global option to enable strict html parsing.
                        ]);

                        $urlArray = explode('/',$company->href);

                        $this->basis_id = end($urlArray);

                        $profile->loadFromUrl($company->href);
                        $tests = $profile->find('.bodytext');
                        $collect = false;
                        $columnName = '';
                        $companyEmail = true;



                        foreach ($tests as $test)
                        {

                            $width = $test->getAttribute('width');

                            if($width == 200)
                            {
                                $font = $test->find('font');
                                if(in_array(trim($font->text),$neededInfo))
                                {
                                    //$this->info($font->text);
                                    $columnName = trim($font->text);
                                    $collect = true;
                                }

                            }

                            if($collect)
                            {
                                if($width == 520)
                                {
                                    $font = $test->find('font');

                                    switch ($columnName)
                                    {
                                        case 'Company Name':
                                            $this->c_name = $font->text;
                                            //$this->info($font->text);
                                            break;
                                        case 'Year of establishment':
                                            $this->c_establishment = $font->text;
                                           // $this->info($font->text);
                                            break;
                                        case 'Address':
                                            $this->c_address = $font->text;
                                            break;
                                        case 'Contact No.':
                                            $this->c_contact = $font->text;
                                            break;
                                        case 'E-mail':
                                            if($companyEmail)
                                            {
                                                $this->c_email = $font->text;
                                                $companyEmail = false;
                                            }else {
                                                $this->cc_email = $font->text;
                                            }
                                            break;
                                        case 'Company website':
                                            $this->c_website = $font->text;
                                            break;
                                        case 'Name':
                                            $this->cc_name = $font->text;
                                            break;
                                        case 'Designation':
                                            $this->cc_designation = $font->text;
                                            break;
                                        case 'Mobile':
                                            $this->cc_mobile = $font->text;
                                            break;
                                        case 'BASIS Membership No.':
                                            $this->c_member_id = $font->text;
                                            break;
                                        default:
                                            $this->info($font->text);
                                            break;
                                    }


                                    $collect = false;


                                }
                            }

                        }

                        $this->save();
                        $counter++;
                        $this->info('Completd - '.$validLink->text.' ID-'.$this->basis_id);

                        //exit();


                    }



                }

                $this->info($counter);



            }

            $minLimit+=20;
        }
    }

    private function save()
    {

        try {
            $member = new Member();
            $member->basis_id = $this->basis_id;
            $member->c_member_id = $this->c_member_id;
            $member->c_name = $this->c_name;
            $member->c_establishment = $this->c_establishment;
            $member->c_address = $this->c_address;
            $member->c_contact = $this->c_contact;
            $member->c_email = $this->c_email;
            $member->c_website = $this->c_website;
            $member->cc_name = $this->cc_name;
            $member->cc_designation = $this->cc_designation;
            $member->cc_mobile = $this->cc_mobile;
            $member->cc_email = $this->cc_email;

            $member->save();
        } catch (\PDOException $e) {
            $this->info($e->getMessage());
        }

    }
}
