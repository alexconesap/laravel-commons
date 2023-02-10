<?php

namespace Tests;

use Alexconesap\Commons\Traits\StackTracer;

class StackTracerTest extends TestCase
{

    public function testValidatorTraitEmpty()
    {
        $g = new class {

            use StackTracer;

            public function t1()
            {
                return $this->getDebugTraceAsArray();
            }

            public function t2()
            {
                return $this->getCallerMethod();
            }

            public function t3()
            {
                return $this->getCallerMethod(10);
            }

            public function t4_params($p1, $param2, $param3, ...$params)
            {
                return $this->getCallerMethod(10);
            }
        };

        // We just verify that it works and not raise an exception
        $g->t1();

        $this->assertEquals(
            'Tests\StackTracerTest->testValidatorTraitEmpty()',
            $g->t2()
        );
        $this->assertEquals(
            'Tests\StackTracerTest->testValidatorTraitEmpty()',
            $g->t3()
        );
        $this->assertEquals(
            'Tests\StackTracerTest->testValidatorTraitEmpty()',
            $g->t4_params('param_1', 22222, 33333, 4, 5, 6)
        );
    }

}
