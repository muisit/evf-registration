<?php

namespace Tests\Unit\App\Support;

use Tests\Unit\TestCase;

class HelpersTest extends TestCase
{
    public function testValidateString()
    {
        $this->assertEquals("", validate_string(null));
        $this->assertEquals("", validate_string(10));
        $this->assertEquals("", validate_string([]));
        $this->assertEquals("", validate_string((object)[]));
        $this->assertEquals("", validate_string((object)["a" => 1]));

        $this->assertEquals("", validate_string(""));
        $this->assertEquals("a", validate_string("a"));
        $this->assertEquals("", validate_string(mb_convert_encoding("ščçøüŌ", "iso-8859-1", "utf-8")));

        $this->assertEquals("ščçøüŌ", validate_string("ščçøüŌ"));
    }

    public function testValidateInt()
    {
        $this->assertEquals(-1, validate_int(null));
        $this->assertEquals(-1, validate_int([]));
        $this->assertEquals(-1, validate_int((object)(["a" => 1])));
        $this->assertEquals(-1, validate_int(""));

        $this->assertEquals(0, validate_int(0));
        $this->assertEquals(-1, validate_int(-1));
        $this->assertEquals(-1, validate_int(-1.2));
        $this->assertEquals(-1, validate_int(-1.999));
        $this->assertEquals(1, validate_int(1.999));
        $this->assertEquals(10, validate_int(10));

        $this->assertEquals(0, validate_int("0"));
        $this->assertEquals(-1, validate_int("-1"));
    }

    public function testValidateTrim()
    {
        $this->assertEquals("", validate_trim(null));
        $this->assertEquals("", validate_trim(10));
        $this->assertEquals("", validate_trim([]));
        $this->assertEquals("", validate_trim((object)[]));
        $this->assertEquals("", validate_trim((object)["a" => 1]));

        $this->assertEquals("ščçøüŌ", validate_trim("ščçøüŌ"));
        $this->assertEquals("ščçøüŌ", validate_trim("    ščçøüŌ     "));
        $this->assertEquals("ščçøüŌ", validate_trim("ščçøüŌ     "));
        $this->assertEquals("ščçøüŌ", validate_trim("     ščçøüŌ"));
        $this->assertEquals("š č ç ø ü Ō", validate_trim(" š č ç ø ü Ō "));
    }

    public function testValidateName()
    {
        $this->assertEquals("", validate_name(null));
        $this->assertEquals("", validate_name(10));
        $this->assertEquals("", validate_name([]));
        $this->assertEquals("", validate_name((object)[]));
        $this->assertEquals("", validate_name((object)["a" => 1]));

        $this->assertEquals("ščçøüŌ", validate_name("ščçøüŌ"));
        $this->assertEquals("0123456789", validate_name("0123456789"));
        $this->assertEquals("abcdefghijklmnopqrstuvwxyz", validate_name("abcdefghijklmnopqrstuvwxyz"));
        $this->assertEquals("ABCDEFGHIJKLMNOPQRSTUVWXYZ", validate_name("ABCDEFGHIJKLMNOPQRSTUVWXYZ"));

        // implicit trim
        $this->assertEquals("-' .", validate_name(" -' . "));

        // replace other characters
        $this->assertEquals("", validate_name("_\r_\n_\f_\t_"));
        $this->assertEquals("", validate_name("`~!@#$%^&*()+={[}]|\\:;\"<,>/?"));
    }

    public function testValidateEmail()
    {
        // non-string cases
        $this->assertEquals(null, validate_email(null));
        $this->assertEquals(null, validate_email(10));
        $this->assertEquals(null, validate_email([]));
        $this->assertEquals(null, validate_email((object)[]));
        $this->assertEquals(null, validate_email((object)["a" => 1]));

        // basic
        $this->assertEquals("a@b.com", validate_email("a@b.com"));
        $this->assertEquals("a+b+c@d.e.f.g.com", validate_email("a+b+c@d.e.f.g.com"));
        $this->assertEquals("a@321.a123", validate_email("a@321.a123"));

        // implicit trim
        $this->assertEquals("a@b.com", validate_email("  a@b.com  "));

        // invalid top domain
        $this->assertEquals("a@b.c", validate_email("a@b.c")); // apparently allowed
        $this->assertEquals(null, validate_email("a@b"));
        $this->assertEquals(null, validate_email("a@com"));

        // missing @, too many @
        $this->assertEquals(null, validate_email("a.b.c"));
        $this->assertEquals(null, validate_email("a@b@c.com"));

        // no spaces, consecutive dots
        $this->assertEquals(null, validate_email("a b@c.com"));
        $this->assertEquals(null, validate_email("ab@c d.com"));
        $this->assertEquals(null, validate_email("a..b@cd.com"));
        $this->assertEquals(null, validate_email("ab@c..d.com"));
    }

    public function testValidateIntList()
    {
        $this->assertEquals([1,2,3], validate_intlist([1,2,3]));
        $this->assertEquals([1,2,3], validate_intlist(['1',"2",3]));
        $this->assertEquals([1,2,3], validate_intlist('["1","2",3]'));
        $this->assertEquals([1,2,3], validate_intlist("'1',\"2\",3"));
        $this->assertEquals([1,2,3], validate_intlist("         '1',\"2\",3       "));

        $this->assertEquals([], validate_intlist(null));
        $this->assertEquals([], validate_intlist(10));
        $this->assertEquals([0], validate_intlist('aaaa'));
        $this->assertEquals([], validate_intlist([]));
        $this->assertEquals([], validate_intlist((object)[]));
        $this->assertEquals([], validate_intlist((object)["a" => 1]));

        $this->assertEquals([], validate_intlist([[]]));
        $this->assertEquals([0,0], validate_intlist([(object)['a' => 1], null, new \stdClass()]));
    }

    public function testBase64EncodeUrl()
    {
        $this->assertEquals("MDFwcXJzdHV2d3h5esOkxZ_DuMO8w5xnZw", base64_encode_url("01pqrstuvwxyzäşøüÜgg"));
        $this->assertEquals("Pj4-Pg", base64_encode_url(">>>>"));
    }

    public function testBase64DecodeUrl()
    {
        $this->assertEquals("01pqrstuvwxyzäşøüÜgg", base64_decode_url("MDFwcXJzdHV2d3h5esOkxZ_DuMO8w5xnZw"));
        $this->assertEquals(">>>>", base64_decode_url("Pj4-Pg"));
    }

    public function testCsrfToken()
    {
        $this->session([])->assertNotEmpty(csrf_token());
    }

    public function testIsEmptyResult()
    {
        $this->assertTrue(emptyResult(null));
        $this->assertTrue(emptyResult(0));
        $this->assertTrue(emptyResult([]));
        $this->assertTrue(emptyResult(''));

        $this->assertTrue(emptyResult((object)[]));
        $this->assertFalse(empty((object)[]));

        $this->assertTrue(emptyResult(collect([])));
        $this->assertFalse(empty(collect([])));
    }
}
