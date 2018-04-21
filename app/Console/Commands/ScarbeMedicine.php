<?php

namespace App\Console\Commands;

use App\Medicine;
use Illuminate\Console\Command;
use PHPHtmlParser\Dom;

class ScarbeMedicine extends Command
{
    protected $name;
    protected $generic;
    protected $dosage_form;
    protected $manufacturer;
    protected $price;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scarb:medicine';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scarbe Medicine from BDrugs';

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
        $basisUrl = 'http://www.bddrugs.com/';

        $maxLimit = 14;
        $minLimit = 1;


        $dom = new Dom();
        $dom->setOptions([
            'cleanupInput' => true, // Set a global option to enable strict html parsing.
        ]);


        $counter = $minLimit;
        while ($minLimit<=$maxLimit)
        {
            $dom->loadFromUrl($basisUrl.'product2.php?idn='.$minLimit);
            $productTypes = $dom->find("table[bgcolor=#cccccc] a");

            foreach ($productTypes as $productType)
            {

                $productTypeLink = $productType->getAttribute('href');

                $this->info('--------------'.$productTypeLink.'------------');

                $dom->loadFromUrl($basisUrl.$productTypeLink);

                $productforDieseases  = $dom->find("table[bgcolor=#cccccc] a");

                foreach ($productforDieseases as $productforDiesease)
                {
                    $productforDieseaseLink = $productforDiesease->getAttribute('href');

                    $this->info('======'.$productforDieseaseLink.'=====');

                    $dom->loadFromUrl($basisUrl.$productforDieseaseLink);

                    $generics = $dom->find("table[bgcolor=#cccccc] a");

                    foreach ($generics as $generic)
                    {
                        $link=$generic->getAttribute('href');


                        if(str_contains($link,'product5'))
                        {
                            $this->info('.......===........'.$link);

                            $dom->loadFromUrl($basisUrl.$link);

                            $products = $dom->find("table[bgcolor=#999999] tr");

                            foreach ($products as $product)
                            {
                                $bgcolor = $product->getAttribute('bgcolor');

                                if($bgcolor =='#CCCCCC')
                                    continue;

                                //$this->info($bgcolor);
                                //Grab Product related information
                                $name = $product->find('td')[0];
                                $this->name = trim($name->firstChild()->text);

                                $this->generic = $product->find('td')[1]->text;
                                $this->dosage_form = $product->find('td')[2]->text;

                                $manufacturer = $product->find('td')[3];
                                $this->manufacturer = trim($manufacturer->firstChild()->text);

                                $this->price = $product->find('td')[4]->text;

                                $this->info($minLimit.'^^^^'.$this->name.'---'.$this->generic.'---'.$this->dosage_form.'---'.$this->manufacturer.'---'.$this->price);

                                $this->save();

                            }
                        }

                    }
                }


            }



            $minLimit++;
        }
    }

    private function save()
    {

        try {
            $medicine = new Medicine();
            $medicine->name = $this->name;
            $medicine->generic = $this->generic;
            $medicine->dosage_form = $this->dosage_form;
            $medicine->manufacturer = $this->manufacturer;
            $medicine->price = $this->price;
            $medicine->save();
        } catch (\PDOException $e) {
            $this->info($e->getMessage());
        }

    }
}
