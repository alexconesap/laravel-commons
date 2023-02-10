<?php

namespace Tests;

use Alexconesap\Commons\StringLib;

class StringLibTest extends TestCase
{

    public function testStringQuoting()
    {
        $this->assertEquals('"1234567890"', StringLib::quoted('1234567890'));
        $this->assertEquals('""', StringLib::quoted(''));
    }

    public function testMSR()
    {
        $this->assertEquals('1234567890', StringLib::cleanMSRCodes('%1234567890_'));
        $this->assertEquals('1234567890', StringLib::cleanMSRCodes(';1234567890$%'));
        $this->assertEquals('1234567890', StringLib::cleanMSRCodes('A1234567890B'));
        $this->assertEquals('1234567890', StringLib::cleanMSRCodes('A1234567890BE%;_'));
    }

    public function testStripTags()
    {
        $text = "<div>We love you<p>!</p><br><br/><br>Really!</div>";
        $r = StringLib::strip_tags_but_brascr($text);
        $this->assertEquals("We love you!\n\n\nReally!", $r);

        $text = "We love you!Really!";
        $r = StringLib::strip_tags_but_brascr($text);
        $this->assertEquals($text, $r);
    }

    /*
     * Expected result
     * "2020-05-01": {
     * "store_1": {
     * "pos_cc": 40,
     * "pos_gc": 40,
     * "app_gc": 40,
     * "app_delivery_gc": 40
     * }
     * },
     * "2020-01-01": {
     * "store_1": {
     * "pos_cc": 21,  <<< sum
     * "pos_gc": 42,  <<< sum
     * "app_gc": 63,  <<< sum
     * "app_delivery_gc": 84  <<< sum
     * },
     * "store_2": {
     * "pos_cc": 15,
     * "pos_gc": 25,
     * "app_gc": 35,
     * "app_delivery_gc": 45
     * }
     * },
     * "2020-02-02": {
     * "store_3": {
     * "pos_cc": 25,
     * "pos_gc": 35,
     * "app_gc": 45,
     * "app_delivery_gc": 55
     * }
     * }
     */
    public function testArraySumByKeys()
    {
        $a_cob['2020-05-01']['store_1'] = [
            'pos_cc' => 40,
            'pos_gc' => 40,
            'app_gc' => 40,
            'app_delivery_gc' => 40,
        ];
        $a_cob['2020-01-01']['store_1'] = [
            'pos_cc' => 10,
            'pos_gc' => 20,
            'app_gc' => 30,
            'app_delivery_gc' => 40,
        ];
        $a_tpag['2020-01-01']['store_1'] = [
            'pos_cc' => 11,
            'pos_gc' => 22,
            'app_gc' => 33,
            'app_delivery_gc' => 44,
        ];
        $a_tpag['2020-01-01']['store_2'] = [
            'pos_cc' => 15,
            'pos_gc' => 25,
            'app_gc' => 35,
            'app_delivery_gc' => 45,
        ];
        $a_tpag['2020-02-02']['store_3'] = [
            'pos_cc' => 25,
            'pos_gc' => 35,
            'app_gc' => 45,
            'app_delivery_gc' => 55,
        ];

        $array = StringLib::array_sum_by_keys(
            array_merge_recursive($a_cob, $a_tpag),
            ['pos_cc', 'pos_gc', 'app_gc', 'app_delivery_gc']
        );

        $this->assertEquals(3, count($array), 'The summarized count of elements should be 3: 3 different dates');

        // Not sum due to they are not same keys to sum
        $this->assertEquals(40, $array['2020-05-01']['store_1']['pos_cc']);

        // Sum due to for that date/store the array_merge_recursive() created pos_cc, pos_gc, etc as an array with 2 values
        $this->assertEquals(10 + 11, $array['2020-01-01']['store_1']['pos_cc']);
        $this->assertEquals(20 + 22, $array['2020-01-01']['store_1']['pos_gc']);
        $this->assertEquals(30 + 33, $array['2020-01-01']['store_1']['app_gc']);
        $this->assertEquals(40 + 44, $array['2020-01-01']['store_1']['app_delivery_gc']);
    }

    public function testStartsAndEndsWith()
    {
        $this->assertTrue(StringLib::startsWith('xsomething', 'x'));
        $this->assertTrue(StringLib::endsWith('xsomething', 'g'));
        $this->assertTrue(StringLib::startsWith('xsomething', 'xs'));
        $this->assertTrue(StringLib::endsWith('xsomething', 'ing'));
        $this->assertFalse(StringLib::startsWith('xsomething', 'g'));
        $this->assertFalse(StringLib::endsWith('xsomething', 'x'));
    }

    public function testLeftAndRightFunctions()
    {
        $this->assertEquals("", StringLib::left(null, -5));
        $this->assertEquals("", StringLib::left('', -5));
        $this->assertEquals("", StringLib::left(null, 3335));
        $this->assertEquals("", StringLib::left('', 1225));
        $this->assertEquals("A sam", StringLib::left('A sample text', 5));
        $this->assertEquals("A sample", StringLib::left('A sample text', -5));
        $this->assertEquals("A sample text", StringLib::left('A sample text', 10000));

        $this->assertEquals("", StringLib::right(null, 2235));
        $this->assertEquals("", StringLib::right('', 15));
        $this->assertEquals("A sample text", StringLib::right('A sample text', 200));
        $this->assertEquals("xt", StringLib::right('A sample text', 2));
        $this->assertEquals("sample text", StringLib::right('A sample text', -2));
    }

    public function testTruncate()
    {
        $this->assertEquals("The long s...", StringLib::truncateString('The long string to truncate somehow', 10));
        $this->assertEquals("TheLongStr...", StringLib::truncateString('TheLongStringToTruncateSomehow', 10));
        $this->assertEquals("TheLongStr...", StringLib::truncateString('TheLongStringToTruncateSomehow', -10));
        $this->assertEquals("somehow", StringLib::truncateString('somehow', 10));
        $this->assertEquals("somehow", StringLib::truncateString('somehow', -10));
        $this->assertEquals("", StringLib::truncateString(null, 10));
        $this->assertEquals("", StringLib::truncateString(null, -10));
        $this->assertEquals("", StringLib::truncateString('', 10));
        $this->assertEquals("The long string that will not be truncated", StringLib::truncateString('The long string that will not be truncated', 10000));

        $this->assertEquals('', StringLib::insertStringInPos(null, null, 10));
        $this->assertEquals('-', StringLib::insertStringInPos(null, '-', 10));
        $this->assertEquals('A sample text-', StringLib::insertStringInPos('A sample text', '-', 50));
        $this->assertEquals('A sample- text', StringLib::insertStringInPos('A sample text', '-', 8));
    }

    public function testEAN13generation()
    {
        $barcode1 = StringLib::generaEAN13('2222');
        $this->assertEquals('2000000022224', $barcode1, 'EAN13 must match');

        $barcode2 = StringLib::generaEAN13('1111');
        $this->assertEquals('2000000011112', $barcode2, 'EAN13 must match');

        $barcode3 = StringLib::generaEAN13('0');
        $this->assertEquals('2000000000000', $barcode3, 'EAN13 must match');

        $barcode4 = StringLib::generaEAN13('1');
        $this->assertEquals('2000000000019', $barcode4, 'EAN13 must match');

        $barcode5 = StringLib::generaEAN13('error');
        $this->assertEquals('2000000000000', $barcode5, 'EAN13 must replace all non digits to 0');

        $barcode6 = StringLib::generaEAN13('1234567890123');
        $this->assertEquals('2123456789014', $barcode6, 'EAN13 must be 2 + 12345678901 + CRC');

        $barcode7 = StringLib::generaEAN13('12345678901');
        $this->assertEquals('2123456789014', $barcode7, 'EAN13 must be 2 + 12345678901 + CRC');

        $barcode8 = StringLib::generaEAN13('1234567890');
        $this->assertEquals('2012345678905', $barcode8, 'EAN13 must be 20 + 1234567890 + CRC');

        $barcode9 = StringLib::generaEAN13('12345678901234567890101010010100101');
        $this->assertEquals('2123456789014', $barcode9, 'EAN13 must be 2 + 12345678901 + CRC');
    }

    public function testPaddingStrings()
    {
        $test_str = 'Alex';

        $test1 = StringLib::padLeft($test_str, 8, '0');
        $this->assertEquals('0000Alex', $test1, 'Must match');

        $test2 = StringLib::padLeft($test_str, 2, '0');
        $this->assertEquals('Alex', $test2, 'Must match');

        $test3 = StringLib::padLeft($test_str, 4, '0');
        $this->assertEquals('Alex', $test3, 'Must match');

        $test4 = StringLib::padLeft($test_str, -4, '0');
        $this->assertEquals('Alex', $test4, 'Must match');

        $test4b = StringLib::padLeft(null, -4, '0');
        $this->assertEquals('', $test4b);

        $test4c = StringLib::padLeft('', -4, '0');
        $this->assertEquals('', $test4c);

        $test4d = StringLib::padLeft(null, 4, '0');
        $this->assertEquals('0000', $test4d);

        // RIGHT
        $rTest1 = StringLib::padRight(null, 4, '0');
        $this->assertEquals('0000', $rTest1);

        $rTest1 = StringLib::padRight('', 4, '0');
        $this->assertEquals('0000', $rTest1);

        $rTest2 = StringLib::padRight($test_str, 6, '0');
        $this->assertEquals('Alex00', $rTest2);
    }

    public function testEnsureLength()
    {
        $test_null = StringLib::ensureLength(null, 4);
        $this->assertEquals('    ', $test_null);

        $test_null = StringLib::ensureLength(null, -1);
        $this->assertEquals('', $test_null);

        $test_str = 'Alex';

        $test = StringLib::ensureLength($test_str, 4, '0');
        $this->assertEquals('Alex', $test, 'Must match');

        $test2 = StringLib::ensureLength($test_str, 6, '0');
        $this->assertEquals('0Alex0', $test2, 'Must match');

        $test3 = StringLib::ensureLength($test_str, 6);
        $this->assertEquals(' Alex ', $test3, 'Must match');

        $test4 = StringLib::ensureLength($test_str, 2);
        $this->assertEquals('Al', $test4, 'Must match');

        $test_left = StringLib::ensureLengthLeft($test_str, 8);
        $this->assertEquals('    Alex', $test_left, 'Must match');

        $test_leftb = StringLib::ensureLengthLeft($test_str, 4);
        $this->assertEquals('Alex', $test_leftb, 'Must match');
    }

    public function testSlugs()
    {
        $this->assertEquals("wow-it-is-a-slug", StringLib::slug('wow it is a slug'));
        $this->assertEquals("", StringLib::slug(''));
        $this->assertEquals("", StringLib::slug(null));
    }

    public function testEnglishNounsPluralsAndSingulars()
    {
        $this->assertEquals('country', StringLib::singularize('countries'));
        $this->assertEquals('stat', StringLib::singularize('stats'));
        $this->assertEquals('other', StringLib::singularize('other'));
        $this->assertNull(StringLib::singularize(null));

        $this->assertEquals('countries', StringLib::pluralize('country'));
        $this->assertEquals('stats', StringLib::pluralize('stat'));
        $this->assertNull(StringLib::pluralize(null));
    }

    public function testArrayToString()
    {
        $array = ['One', 'Element', 'Sub Array' => ['With some', 'other' => 'Elements']];
        $this->assertEquals("One Element With some Elements", StringLib::arrayToString($array));
        $this->assertEquals("One-Element-With some-Elements", StringLib::arrayToString($array, '-'));
        $this->assertEquals("OneElementWith someElements", StringLib::arrayToString($array, ''));
        $this->assertEquals('', StringLib::arrayToString(null, ','));
    }

    public function testIsUTF8()
    {
        $this->assertTrue(StringLib::isUTF8('111'));
        $this->assertTrue(StringLib::isUTF8(2222));
        $this->assertTrue(StringLib::isUTF8('áéçáñ!🐰💗🐰'));
    }

    public function testNumberConversions()
    {
        $this->assertEquals("111,22", StringLib::floatToString(111.22));
        $this->assertEquals("111,22", StringLib::floatToString(111.22, ','));
        $this->assertEquals("111.22", StringLib::floatToString(111.22, '.'));
        $this->assertEquals("0,00", StringLib::floatToString(null));

        $this->assertEquals(2111.22, StringLib::stringToFloat('2.111,22'));
        $this->assertEquals(111.22, StringLib::stringToFloat('111,22'));
        $this->assertEquals(0, StringLib::stringToFloat(null));
        $this->assertEquals(0, StringLib::stringToFloat(''));
    }

    public function testTokenizer()
    {
        $test_str = 'Alex|is|testing|';

        $test = StringLib::getToken($test_str, 0, ';');
        $this->assertEquals($test_str, $test, 'Must match because separator is not ok');

        $test1 = StringLib::getToken($test_str, 1, ';');
        $this->assertEquals('', $test1, 'Must match because separator is not ok and token num is >0');

        $test2 = StringLib::getToken($test_str, 0, '|');
        $this->assertEquals('Alex', $test2, 'Must match to first token');

        $test3 = StringLib::getToken($test_str, 1, '|');
        $this->assertEquals('is', $test3, 'Must match to second token');

        $test4 = StringLib::getToken($test_str, 2, '|');
        $this->assertEquals('testing', $test4, 'Must match to third token');

        $test5 = StringLib::getToken($test_str, 3, '|');
        $this->assertEquals('', $test5, 'Must match when above the max number of tokens');

        $test7 = StringLib::getToken($test_str, -55, '|');
        $this->assertEquals('', $test7, 'Must match to nothing in case of negative token number');

        $test_str2 = '|Alex|is|testing|';
        $test10 = StringLib::getToken($test_str2, 0, '|');
        $this->assertEquals('', $test10, 'Must match nothing at the left of first token');

        $test11 = StringLib::getToken($test_str2, 1, '|');
        $this->assertEquals('Alex', $test11, 'Must match the second token');

        $test12 = StringLib::getToken(null, 1, '|');
        $this->assertEquals('', $test12);
    }

    public function testRemoveEmojis()
    {
        $san = StringLib::removeAccentsAndSymbols("Virtual Gift Card for Lucy Art!🐰💗🐰");
        $this->assertEquals("Virtual Gift Card for Lucy Art!🐰💗🐰", $san);

        $san = StringLib::removeNonASCIIChars("Virtual Gift Card for Lucy Art!🐰💗🐰");
        $this->assertEquals('Virtual Gift Card for Lucy Art!', $san);

        $san = StringLib::removeNonASCIIChars("áéçáñ!🐰💗🐰");
        $this->assertEquals('!', $san);
    }

    public function testIdentifiers()
    {
        $uid1 = StringLib::GUID();
        $this->assertEquals(36, strlen($uid1));

        $uid1b = StringLib::GUID('');
        $this->assertEquals(32, strlen($uid1b));

        $uid2 = StringLib::getUIDFromTimestamp('xx', 10, true);
        $this->assertEquals(10, strlen($uid2));

        $uid2b = StringLib::getUIDFromTimestamp('xxx', 20, true);
        $this->assertEquals(13, strlen($uid2b), "Should be 10 + 3 for the x characters");

        $pin = StringLib::PIN();
        $this->assertEquals(4, strlen($pin));

        $pinb = StringLib::PIN(6);
        $this->assertEquals(6, strlen($pinb));
    }

    public function testClassNameToWords()
    {
        $this->assertEquals('String Lib Test', StringLib::classNameToWords(get_class($this)));
    }

    public function testArrayRecursiveLookup()
    {
        $this->assertTrue(
            StringLib::in_array_r(
                'test',
                ['key1' => ['key2' => ['key3' => 'test']]]
            )
        );
        $this->assertTrue(
            StringLib::in_array_r(
                'test',
                ['key1' => 'test']
            )
        );
        $this->assertFalse(
            StringLib::in_array_r(
                'test',
                ['test' => 'x']
            )
        );
    }

    public function testStringReplacements()
    {
        $this->assertNull(StringLib::replaceBracedVariables(null, []));

        $this->assertEquals('This {{x1}}', StringLib::replaceBracedVariables('This {{x1}}', []));
        $this->assertEquals('This is a test', StringLib::replaceBracedVariables('This {{x1}}', ['x1' => "is a test"]));

        $this->assertEquals('This is again is', StringLib::replaceBracedVariables('This {{  x1  }} again {{x1}}', ['x1' => "is"]));
        $this->assertEquals('This is another test', StringLib::replaceBracedVariables('This {{  x1  }} {{x2  }} test', ['x1' => "is", 'x2' => 'another']));
    }

    public function testGetBetween()
    {
        // String result
        $this->assertEquals('primero', StringLib::getBetween('{primero} and other {segundo}', '{', '}'));

        $this->assertEquals('primero', StringLib::getBetween('Example {primero} and other {segundo}', '{', '}'));
        $this->assertEquals('segundo', StringLib::getBetween('Example {primero} and other {segundo}', '{', '}', 1));
        $this->assertEquals('', StringLib::getBetween('Example {primero} and other {segundo}', '{', '}', 2));

        $this->assertEquals('primero and other ', StringLib::getBetween('Example {primero and other {segundo', '{', '}'));
        $this->assertEquals('segundo', StringLib::getBetween('Example {primero and other {segundo', '{', '}', 1));
        $this->assertEquals('', StringLib::getBetween('Example {primero and other {segundo', '{', '}', 2));

        $this->assertEquals('primero', StringLib::getBetween('Example {{primero}} and other {{segundo}}', '{{', '}}'));

        $this->assertEquals('primero', StringLib::getBetween('Example <primero> and other <segundo>', '<', '>'));

        $this->assertEquals('', StringLib::getBetween(null, '<', '>'));

        // Array result
        $this->assertEquals([], StringLib::getABetween(null, '{', '}'));
        $this->assertEquals([], StringLib::getABetween('', '{', '}'));
        $this->assertEquals([], StringLib::getABetween('It does not include any match', '{', '}'));
        $this->assertEquals(['1|2|3|4', '5|6|7|8'], StringLib::getABetween('Text in front {1|2|3|4}{5|6|7|8} Text after', '{', '}'));
        $this->assertEquals(['token1', 'token2'], StringLib::getABetween('This {token1} test {token2} must work', '{', '}'));
        $this->assertEquals(['1|2|3|4', '5|6|7|8'], StringLib::getABetween('Text in front {1|2|3|4}{5|6|7|8}', '{', '}'));
        $this->assertEquals(['1|2|3|4', '5|6|7|8'], StringLib::getABetween('{1|2|3|4}{5|6|7|8}', '{', '}'));
        $this->assertEquals(['1|2|3|4', '5|6|7|8'], StringLib::getABetween('{{1|2|3|4}}  {{5|6|7|8}}', '{{', '}}'));

        $this->assertEquals(['22222|Product|0||100.00|1.00|2.00|3.00|1899-12-30|02:44|A|B'], StringLib::getABetween('[LOYDETAIL|0000|1899-12-30|02:44|20111229023808139|2011E1C0001S1T59||0.00|{22222|Product|0||100.00|1.00|2.00|3.00|1899-12-30|02:44|A|B}]', '{', '}'));
    }

    public function testToFirebirdString()
    {
        $this->assertEquals('áx', StringLib::toFirebirdString("&aacute;x"));
        $this->assertEquals('*xx', StringLib::toFirebirdString('&bull;xx'));

        $this->assertEquals('1234eeaa567890', StringLib::toFirebirdString('1234éëáä567890'));
        $this->assertEquals('1234567890', StringLib::toFirebirdString('1\\234567890'));
        $this->assertEquals('12\'\'34E567890', StringLib::toFirebirdString('1\\2\'\'34€567890'));
        $this->assertEquals('1234eeaa567890', StringLib::toFirebirdString('1234éëáä567890'));
    }

    public function testCleanInvalidCharsForFirebird()
    {
        $text_conv = StringLib::sanitizeASCII("ÃÂ Atitlán catuaí");
        $this->assertEquals('AAA Atitlan catuai', $text_conv);

        $text_conv = StringLib::toFirebirdString("ÃÂ ");
        $this->assertEquals('AAA ', $text_conv);

        $text_conv = StringLib::sanitizeASCII('Café Noir/Grand');
        $this->assertEquals('Cafe Noir/Grand', $text_conv);

        $text_conv = StringLib::sanitizeASCII('Häagen Dazs Café');
        $this->assertEquals('Haagen Dazs Cafe', $text_conv);

        $text_conv = StringLib::sanitizeASCII('Brownies crème');
        $this->assertEquals('Brownies creme', $text_conv);

        $text_conv = StringLib::sanitizeASCII('Brownies crème€');
        $this->assertEquals('Brownies cremeE', $text_conv);

        $text_conv = StringLib::sanitizeASCII('Test💗');
        $this->assertEquals('Test?', $text_conv);

        $text_conv = StringLib::sanitizeASCII(null);
        $this->assertEquals('', $text_conv);

        $text_conv = StringLib::sanitizeASCII('💗💗💗💗');
        $this->assertEquals('????', $text_conv);
    }
}
