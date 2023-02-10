<?php

namespace Tests;

use Alexconesap\Commons\ArrayList;

class ArrayListTest extends TestCase
{
    public function testCreationEmpty()
    {
        $a1 = new ArrayList();
        $this->assertNotNull($a1, 'Must exists at least as an empty object 1!');

        $a2 = new ArrayList([]);
        $this->assertNotNull($a2, 'Must exists at least as an empty object 2!');

        $this->expectException(\TypeError::class);
        $a3 = new ArrayList(null);

        $elements3 = ['one', 'two', 'three'];
        $elements5 = ['one', 'two', 'three', 'four', 'five'];

        $this->assertEquals(count($elements3), 3, 'Element count mismatch? 3');
        $this->assertEquals(count($elements5), 5, 'Element count mismatch? 5');

        $a4 = new ArrayList($elements3);
        $this->assertNotNull($a4, 'Must exists at least as an empty object 3!');
        $this->assertEquals($a4->count(), count($elements3), 'a-Element count must be ' . count($elements3));

        $a4->assign($elements5);
        $this->assertEquals($a4->count(), count($elements5), 'b-Element count must be ' . count($elements5));

        $a4->assign($elements3);
        $this->assertEquals($a4->count(), count($elements3), 'c-Element count must be ' . count($elements3));
    }

    public function testCleaningUp()
    {
        $a4 = new ArrayList();
        $this->assertTrue($a4->isEmpty(), 'It is an empty list');

        $a4->add(['one' => '1']);
        $this->assertFalse($a4->isEmpty(), 'It is NOT an empty list. It must contain one element!');

        $a4->clear();
        $this->assertTrue($a4->isEmpty(), 'It is an empty list once again');

        $a4->add(['one' => '1']);
        $this->assertFalse($a4->isEmpty(), 'It is NOT an empty list. It must contain one element again!');
    }

    public function testCreationAsValueOf()
    {
        $named_elements3 = ['one' => '1', 'two' => '2', 'three' => '3'];
        $a5 = ArrayList::valueOf($named_elements3);
        $this->assertNotNull($a5, 'Must exists at least as an empty object!');
        $this->assertEquals($a5->count(), count($named_elements3), 'a-Element count must be ' . count($named_elements3));
    }

    public function testUsingElements()
    {
        $named_elements3 = ['one' => '1', 'two' => '2', 'three' => '3'];
        $elements5 = ['one', 'two', 'three', 'four', 'five'];

        $a6 = ArrayList::valueOf($named_elements3);
        $this->assertTrue($a6->has('one'), 'The element with key one must exist');
        $this->assertFalse($a6->has('test'), 'The element with key test do not exist');
        $this->assertFalse($a6->has(null), 'The element with key null do not exist');

        $a6->remove('one');
        $this->assertFalse($a6->has('one'), 'The element with key one no longer exists');
        $this->assertEquals($a6->count(), count($named_elements3) - 1, 'a-Element count must be ' . (count($named_elements3) - 1));

        // Restore all elements once again
        $a6->assign($named_elements3);
        $this->assertTrue($a6->has('one'), 'The element with key one must exist');

        // Restore all elements once again
        $a6->replaceAll($named_elements3);
        $this->assertTrue($a6->has('one'), 'The element with key one must exist');

        // This 'add' operation will not add at all
        $a6->add(['one' => '11']);
        $this->assertEquals($a6->count(), count($named_elements3), 'b-Element count must be ' . (count($named_elements3)));

        $a6->add(['test' => '10']);
        $this->assertEquals($a6->count(), count($named_elements3) + 1, 'c-Element count must be ' . (count($named_elements3) + 1));
        $this->assertTrue($a6->has('test'), 'Must have an element named test');

        $a6->addElement('test2', '11');
        $this->assertEquals($a6->count(), count($named_elements3) + 2, 'c-Element count must be ' . (count($named_elements3) + 2));
        $this->assertTrue($a6->has('test2'), 'Must have an element named test2');

        $a6->set('test3', '11');
        $this->assertEquals($a6->count(), count($named_elements3) + 3, 'c-Element count must be ' . (count($named_elements3) + 3));
        $this->assertTrue($a6->has('test3'), 'Must have an element named test3');

        $v = $a6->get('one');
        $this->assertEquals($v, '11', 'Must match');

        $v = $a6->input('one');
        $this->assertEquals($v, '11', 'Must match as input()');

        $v = $a6->post('one');
        $this->assertEquals($v, '11', 'Must match as post()');

        $v = $a6->get('not exist');
        $this->assertNull($v, 'Must be null as it is the default for get');

        $v = $a6->get('not exist', 'xxx');
        $this->assertEquals($v, 'xxx', 'Must match the default value set as xxx');

        $v = $a6->filled('one');
        $this->assertTrue($v, 'The element have value assigned');

        $v = $a6->filled('twenty');
        $this->assertFalse($v, 'The element is empty');

        $a6->set('one', '');
        $v = $a6->filled('one');
        $this->assertFalse($v, 'The element is empty');

        $a6->set('one', '  ');
        $v = $a6->filled('one');
        $this->assertTrue($v, 'The element is not empty because it contains spaces');

        $a6->set('one', 0);
        $v = $a6->filled('one');
        $this->assertTrue($v, 'The element is not empty because it 0');

        $a6->set('one', null);
        $v = $a6->filled('one');
        $this->assertFalse($v, 'The element is not filled because it is null');
    }

    public function testGetSpecial()
    {
        $named_elements3 = [
            'one' => '1',
            'two' => '2_$ax1',
            'three' => 'testing 300',
            'four' => null,
            // For boolean testing
            '5' => '1',
            '6' => '0',
            '7' => '',
            '8' => false,
            '9' => true,
            '10' => "false",
            '11' => "true",
            '12' => 1,
            '13' => 0,
        ];
        $a5 = ArrayList::valueOf($named_elements3);

        $v1 = $a5->getAlpha('one');
        $this->assertEquals('', $v1, 'a-Alpha chars do not match');
        $v1 = $a5->getDigits('one');
        $this->assertEquals('1', $v1, 'b-Digits chars do not match');
        $v1 = $a5->getInt('one');
        $this->assertEquals(1, $v1, 'c-Int value do not match');
        $v1 = $a5->getAlnum('one');
        $this->assertEquals(1, $v1, 'c2-Alternative chars and numbers value do not match');

        $v1 = $a5->getAlpha('two');
        $this->assertEquals('ax', $v1, 'd-Alpha chars do not match');
        $v1 = $a5->getDigits('two');
        $this->assertEquals('21', $v1, 'e-Digits chars do not match');
        $v1 = $a5->getInt('two');
        $this->assertEquals(2, $v1, 'f-Int value do not match. It must pick only first digits up to one alpha');
        $v1 = $a5->getAlnum('two');
        $this->assertEquals('2ax1', $v1, 'f2-Alternative chars and numbers value do not match');

        $v1 = $a5->getAlpha('three');
        $this->assertEquals('testing', $v1, 'g-Alpha chars do not match');
        $v1 = $a5->getDigits('three');
        $this->assertEquals('300', $v1, 'h-Digits chars do not match');
        $v1 = $a5->getInt('three');
        $this->assertEquals(0, $v1, 'i-Int value do not match. It must pick only first digits up to one alpha');
        $v1 = $a5->getAlnum('three');
        $this->assertEquals('testing300', $v1, 'i2-Alternative chars and numbers value do not match');

        $v1 = $a5->getAlpha('four');
        $this->assertEquals('', $v1, 'j-Alpha chars do not match');
        $v1 = $a5->getDigits('four');
        $this->assertEquals('', $v1, 'k-Digits chars do not match');
        $v1 = $a5->getInt('four');
        $this->assertEquals(0, $v1, 'l-Int value do not match. Must be 0 as it is a null value for key.');
        $v1 = $a5->getAlnum('four');
        $this->assertEquals('', $v1, 'l2-Alternative chars and numbers value do not match');

        // Booleans
        $v1 = $a5->getBoolean('5'); // "1"
        $this->assertTrue($v1, 'a- Must be a boolean true');

        $v1 = $a5->getBoolean('12'); // 1
        $this->assertTrue($v1, 'b- Must be a boolean true');

        $v1 = $a5->getBoolean('6'); // "0"
        $this->assertFalse($v1, 'c- Must be a boolean false');

        $v1 = $a5->getBoolean('13'); // 0
        $this->assertFalse($v1, 'c- Must be a boolean false');

        $v1 = $a5->getBoolean('7'); // ''
        $this->assertFalse($v1, 'd- Must be a boolean false');

        $v1 = $a5->getBoolean('four'); // null
        $this->assertFalse($v1, 'e- Must be a boolean false');

        $v1 = $a5->getBoolean('8'); // false
        $this->assertFalse($v1, 'f- Must be a boolean false');

        $v1 = $a5->getBoolean('10'); // "false"
        $this->assertFalse($v1, 'g- Must be a boolean false');

        $v1 = $a5->getBoolean('9'); // true
        $this->assertTrue($v1, 'h- Must be a boolean true');

        $v1 = $a5->getBoolean('11'); // "true"
        $this->assertTrue($v1, 'i- Must be a boolean true');

        $v1 = $a5->getBoolean('12'); // 1
        $this->assertTrue($v1, 'j- Must be a true boolean based on 1');

        $v1 = $a5->getBoolean('13'); // 0
        $this->assertFalse($v1, 'k- Must be a false boolean based on 0');

        $v1 = $a5->getBoolean('999'); // Not exists!
        $this->assertFalse($v1, 'l- Must be a false boolean based on not existing element');

        $v1 = $a5->getBoolean('7'); // Blank element
        $this->assertFalse($v1, 'o- Must be a false boolean based on blank/empty string');
    }
}
