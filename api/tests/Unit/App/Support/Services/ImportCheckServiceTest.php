<?php

namespace Tests\Unit\App\Support;

use App\Models\Category;
use App\Models\Country;
use App\Models\Fencer;
use App\Models\FencerLabel;
use App\Support\Services\ImportCheckService;
use App\Support\Traits\EVFUser;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class ImportCheckServiceTest extends TestCase
{
    public function testFindEntryForName()
    {
        $this->createFencers(['Mgbrv40', 'Mgbrv50l', 'Mgbrv60l', 'Mgbrv70l', 'Mgerv40l', 'Mgerv50l', 'Mgerv60l', 'Mgerv70l']);
        $cat1 = Category::find(Category::CAT1);
        $cat2 = Category::find(Category::CAT2);
        $cat3 = Category::find(Category::CAT3);
        $cat4 = Category::find(Category::CAT4);

        $service = new ImportCheckService();
        $service->gender = 'M';
        $service->minDate = $cat1->getMinimalDate(new Carbon('2020-01-01'));
        $service->maxDate = $cat1->getMaximalDate(new Carbon('2020-01-01'));
        $service->createCountryCache();

        $gbr = Country::find(Country::GBR);
        $ita = Country::find(Country::ITA);
        $results = $service->findEntryForName('John', 'Wilde', $gbr);
        $this->assertCount(1, $results); // found 1 entry exactly
        $this->assertFalse(isset($results['checks'])); // no comments

        // friend Lee occurs 3 times, all older
        $results = $service->findEntryForName('Lee', 'Wilde', $gbr);
        // we only expect 3 GBR Lee's, all older
        $this->assertCount(3, $results);
        foreach ($results as $res) {
            $this->assertTrue(isset($res['fencer']));
            $this->assertTrue(isset($res['fencer']['fencer_firstname']));
            $this->assertEquals('Lee', $res['fencer']['fencer_firstname']);
            $this->assertEquals(1, $res['fencer']['fencer_country']);
            $this->assertTrue(isset($res['checks']));
            $this->assertTrue(is_array($res['checks']));
            $this->assertCount(1, $res['checks']);
            $this->assertEquals('age', $res['checks'][0]['type']);
        }

        // friend Lee does not live in ITA, but there are GER and GBR relatives
        $results = $service->findEntryForName('Lee', 'Wilde', $ita);
        $this->assertCount(7, $results);
        foreach ($results as $res) {
            $this->assertTrue(isset($res['checks']));
            $this->assertTrue(is_array($res['checks']));
            $this->assertEquals('country', $res['checks'][0]['type']);
        }

        $service->minDate = $cat3->getMinimalDate(new Carbon('2020-01-01'));
        $service->maxDate = $cat3->getMaximalDate(new Carbon('2020-01-01'));
        $results = $service->findEntryForName('Lee', 'Wilde', $ita);
        $this->assertCount(4, $results);

        $results = $service->findEntryForName('Lee', 'Wilde', $gbr);
        \Log::debug("results is " . json_encode($results));
        $this->assertCount(1, $results); // one from exactly this age group, breaks from the internal loop
    }

    public function testFindFencerByNameAndGender()
    {
        $service = new ImportCheckService();
        $service->gender = 'M';
        $res = $service->findFencerByNameAndGender('john', 'testita');
        $this->assertCount(1, $res);

        $this->createFencers(['Mgbrv40', 'Fgbrv40', 'Mgerv40', 'Fgerv40', 'Mgbrv40b', 'Fgbrv40b']);
        $res = $service->findFencerByNameAndGender('john', 'wilde');
        $this->assertCount(1, $res);
        $res = $service->findFencerByNameAndGender('joanna', 'wilde');
        $this->assertCount(0, $res);
        $res = $service->findFencerByNameAndGender('hans', 'ulrich');
        $this->assertCount(1, $res);
        $res = $service->findFencerByNameAndGender('anna', 'ulrich');
        $this->assertCount(0, $res);

        // case insensitive search
        $res = $service->findFencerByNameAndGender('JOHN', 'WILDE');
        $this->assertCount(1, $res);

        // search for different labels
        $res = $service->findFencerByNameAndGender('charles', 'wothersome');
        $this->assertCount(1, $res);
        $res = $service->findFencerByNameAndGender('charly', 'wothersome');
        $this->assertCount(1, $res);
        $res = $service->findFencerByNameAndGender('charles', 'withersome');
        $this->assertCount(1, $res);
        $res = $service->findFencerByNameAndGender('charly', 'withersome');
        $this->assertCount(1, $res);
    }

    public function testFindAllByLabelSound()
    {
        $service = new ImportCheckService();
        $service->gender = 'M';

        $this->createFencers(['Mitav40', 'Mitav50', 'Mgbrv70l', 'Mgbrv50l', 'Mgbrv40l']);
        $this->assertCount(2, $service->findAllByLabelSound('first', 'Ropert'));
        $this->assertCount(2, $service->findAllByLabelSound('last', 'Ashcrift'));
        $this->assertCount(3, $service->findAllByLabelSound('first', 'Lee'));
        $this->assertCount(5, $service->findAllByLabelSound('first', 'Jihn')); // standard John and Joanne fit as well
    }

    public function testFindSuggestions()
    {
        $cat1 = Category::find(Category::CAT1);
        $cat2 = Category::find(Category::CAT2);
        $cat3 = Category::find(Category::CAT3);
        $cat4 = Category::find(Category::CAT4);

        $service = new ImportCheckService();
        $service->gender = 'M';
        $service->minDate = $cat2->getMinimalDate(new Carbon('2020-01-01'));
        $service->maxDate = $cat2->getMaximalDate(new Carbon('2020-01-01'));

        $gbr = Country::find(Country::GBR);
        $ita = Country::find(Country::ITA);
        $ger = Country::find(Country::GER);

        $this->createFencers(['Mitav40', 'Mitav50', 'Mgbrv70l', 'Mgbrv60l', 'Mgbrv50l', 'Mgbrv40', 'Mgbrv40l', 'Mgerv40l', 'Mgerv50l', 'Mgerv60l', 'Mgerv70l']);

        $suggestions = $service->findSuggestions('Rupert', 'Murdoch', $ita);
        // this should return Mitav40 and Mitav50 first, then John and Joanna (all the ITA fencers)
        $this->assertCount(4, $suggestions);
        $this->assertCount(1, $suggestions[0]["checks"]); // incorrect last name
        $this->assertCount(2, $suggestions[1]["checks"]); // incorrect last name, too young
        $this->assertCount(3, $suggestions[2]["checks"]); // incorrect last and first name, too young
        $this->assertCount(3, $suggestions[3]["checks"]); // incorrect last and first name, too young

        $suggestions = $service->findSuggestions('Lee', 'Waldi', $gbr);
        // 3 GBR lee's, 3 GER lee's, young John, young gbr Lee, young ger Lee, 9 total
        $this->assertCount(9, $suggestions);
        $this->assertCount(0, $suggestions[0]["checks"]);
        $this->assertCount(0, $suggestions[1]["checks"]);
        $this->assertCount(0, $suggestions[2]["checks"]);
        $this->assertCount(1, $suggestions[3]["checks"]); // incorrect country
        $this->assertCount(1, $suggestions[4]["checks"]); // incorrect country
        $this->assertCount(1, $suggestions[5]["checks"]); // incorrect country
        $this->assertCount(2, $suggestions[6]["checks"]); // incorrect first name, too young
        $this->assertCount(1, $suggestions[7]["checks"]); // too young
        $this->assertCount(2, $suggestions[8]["checks"]); // incorrect country, too young

        $suggestions = $service->findSuggestions('Lee', 'Ashcraft', $ger);
        // 3 ger Lee's, 1 ita Ashcroft, 3 gbr Lee's, 1 ita Ashcraft,1 gbr Lee, 1 ger Lee
        $this->assertCount(10, $suggestions);
        $this->assertCount(1, $suggestions[0]["checks"]); // incorrect last name
        $this->assertCount(1, $suggestions[1]["checks"]); // incorrect last name
        $this->assertCount(1, $suggestions[2]["checks"]); // incorrect last name
        $this->assertCount(2, $suggestions[3]["checks"]); // incorrect first name, country
        $this->assertCount(2, $suggestions[4]["checks"]); // incorrect last name country
        $this->assertCount(2, $suggestions[5]["checks"]); // incorrect last name country
        $this->assertCount(2, $suggestions[6]["checks"]); // incorrect last name, country
        $this->assertCount(3, $suggestions[7]["checks"]); // incorrect country, first name, too young
        $this->assertCount(3, $suggestions[8]["checks"]); // incorrect last name, country, too young
        $this->assertCount(2, $suggestions[9]["checks"]); // incorrect last name, too young

    }

    private function createFencers($cases)
    {
        // in 2020, people born in 1970 would be V2/V50
        $fencerTestCases = [
            "Mgbrv40" => [["john"], ["wilde"], '1971-01-01', 'M', Country::GBR],
            "Fgbrv40" => [["joanna"], ["wilde"], '1971-01-01', 'F', Country::GBR],
            "Mgerv40" => [["hans"], ["ulrich"], '1971-01-01', 'M', Country::GER],
            "Fgerv40" => [["anna"], ["ulrich"], '1971-01-01', 'F', Country::GER],
            "Mgerv40b" => [["john", 'anna'], ["wilde"], '1971-01-01', 'M', Country::GER],
            "Mgbrv40b" => [["charles",'charly'], ["withersome", 'wothersome'], '1971-01-01', 'M', Country::GBR],
            "Fgbrv40b" => [["charles",'charly'], ["withersome", 'wothersome'], '1971-01-01', 'F', Country::GBR],
            "Mgbrv40l" => [["Lee", 'jahn'], ["wilde"], '1971-01-01', 'M', Country::GBR],
            "Mgbrv50l" => [["Lee", 'jahn'], ["wilde"], '1961-01-01', 'M', Country::GBR],
            "Mgbrv60l" => [["Lee", 'johan'], ['wilde', "wolde"], '1951-01-01', 'M', Country::GBR],
            "Mgbrv70l" => [["Lee", 'jhon'], ['wilde', "wolde"], '1941-01-01', 'M', Country::GBR],
            "Mgerv40l" => [["Lee", 'jahn'], ["wilde"], '1971-01-01', 'M', Country::GER],
            "Mgerv50l" => [["Lee", 'jahn'], ["wilde"], '1961-01-01', 'M', Country::GER],
            "Mgerv60l" => [["Lee", 'johan'], ['wilde', "wolde"], '1951-01-01', 'M', Country::GER],
            "Mgerv70l" => [["Lee", 'jhon'], ['wilde', "wolde"], '1941-01-01', 'M', Country::GER],
            "Mitav40" => [["Robert"], ['Ashcraft'], '1971-01-01', 'M', Country::ITA],
            "Mitav50" => [["Rupert"], ['Ashcroft'], '1961-01-01', 'M', Country::ITA],
        ];

        foreach ($cases as $case) {
            if (isset($fencerTestCases[$case])) {
                $fencer = new Fencer();
                $fencer->fencer_firstname = $fencerTestCases[$case][0][0];
                $fencer->fencer_surname = $fencerTestCases[$case][1][0];
                $fencer->fencer_dob = $fencerTestCases[$case][2];
                $fencer->fencer_gender = $fencerTestCases[$case][3];
                $fencer->fencer_country = $fencerTestCases[$case][4];
                $fencer->save();

                foreach ($fencerTestCases[$case][0] as $l) {
                    $label = new FencerLabel();
                    $label->label = $l;
                    $label->type = 'first';
                    $label->fencer_id = $fencer->getKey();
                    $label->save();
                }

                foreach ($fencerTestCases[$case][1] as $l) {
                    $label = new FencerLabel();
                    $label->label = $l;
                    $label->type = 'last';
                    $label->fencer_id = $fencer->getKey();
                    $label->save();
                }
            }
            else {
                \Log::error("Could not find case for $case");
            }
        }
    }
}
