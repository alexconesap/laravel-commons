<?php

namespace Tests;

use Alexconesap\Commons\Models\BaseBean;
use Alexconesap\Commons\Models\BasicBean;
use Alexconesap\Commons\Models\BasicBeanValidated;

class BaseBeansTest extends TestCase
{

    public function testBasicBeanMagicalMethodsAreCalledProperly()
    {
        $attributes = ['name' => 'Alex',
            'surname' => 'Conesa',
            'city' => 'Barcelona',
            'version' => '1'];

        $g = new class extends BasicBean {

            protected $attributes = [
                'name' => 'Alex',
                'surname' => 'Conesa',
                'city' => 'Barcelona',
            ];
        };

        $this->assertEquals('Alex', $g->name);
        $this->assertEquals('Conesa', $g->surname);
        $this->assertEquals('Barcelona', $g->city);
        $this->assertNull($g->xxxx);

        $this->assertEquals($attributes, $g->toArray());

        $json = json_encode($attributes);
        $this->assertEquals($json, $g->toJson(), 'Verify the json_encoding options');
        $this->assertEquals($json, (string)$g, 'ToString() is currently implemented to return the JSON representation');
    }

    public function testBaseBeanMagicalMethodsAreCalledProperly()
    {
        /**
         * @property string $name
         * @property string $surname
         * @property string $city
         */
        $base_bean_obj = new class extends BaseBean {
            private $name;
            private $surname;
            private $city;

            public function setName($v)
            {
                $this->name = $v;
                return $this;
            }

            public function setSurname($v)
            {
                $this->surname = $v;
                return $this;
            }

            public function setCity($v)
            {
                $this->city = $v;
                return $this;
            }

            public function getName()
            {
                return $this->name;
            }

            public function getSurname()
            {
                return $this->surname;
            }

            public function getCity()
            {
                return $this->city;
            }
        };

        $base_bean_obj->name = 'Alex';
        $base_bean_obj->surname = 'Conesa';
        $base_bean_obj->city = 'Barcelona';

        $this->assertEquals('Alex', $base_bean_obj->name);
        $this->assertEquals('Conesa', $base_bean_obj->surname);
        $this->assertEquals('Barcelona', $base_bean_obj->city);
        $this->assertNull($base_bean_obj->xxxx);
    }

    public function testBasicBeanValidatedIsProperlyConstructed()
    {
        /**
         * @property array $api_result
         * @property Store $stores
         * @property Meta $meta
         * @property string $favorite_id
         */
        $a_bean_validated = new class extends BasicBeanValidated {
            protected $strict_assignment = false;

            protected array $attributes_details = [
                'api_result' => ['mandatory' => true, 'type' => 'array'],
                'stores' => ['mandatory' => true, 'type' => 'collection', 'class' => Store::class],
                'meta' => ['mandatory' => false, 'type' => 'class', 'class' => Meta::class],
                'favorite_id' => ['mandatory' => false, 'type' => 'string'],
            ];

            public function __construct()
            {
                parent::__construct([
                    'api_result' => ['something', 'and', 'something', 'else'],
                    'stores' => [
                        ['id' => '1', 'name' => null],
                        ['id' => '2'],
                        ['id' => '3', 'name' => 'Name2'],
                    ],
                    'meta' => ['pagination' => ['total' => 12, 'count' => 1]],
                    'favorite_id' => 'A favorite',
                ]);
            }

        };

        $this->assertEquals('something|and|something|else', $a_bean_validated->api_result);
        $this->assertEquals('A favorite', $a_bean_validated->favorite_id);
        $this->assertEquals(12, $a_bean_validated->meta->pagination->total);
        $this->assertEquals(1, $a_bean_validated->meta->pagination->count);

        $this->assertCount(3, $a_bean_validated->stores);
        $this->assertEquals('N/A', $a_bean_validated->stores->first()->name);
        $this->assertEquals('Name2', $a_bean_validated->stores->last()->name);

        $this->assertNull($a_bean_validated->xxxx);
    }
}

/**
 * @property string id
 * @property string name
 */
class Store extends BasicBeanValidated
{
    protected array $attributes_details = [
        'id' => ['mandatory' => true, 'type' => 'string'],
        'name' => ['mandatory' => false, 'type' => 'string'],
    ];

    protected array $attributes_defaults = [
        'name' => 'N/A',
    ];

    protected $auto_validate_on_construction = true;
}

/**
 * @property MetaPagination pagination
 */
class Meta extends BasicBeanValidated
{
    protected array $attributes_details = [
        'pagination' => ['mandatory' => false, 'type' => 'class', 'class' => MetaPagination::class],
    ];

    protected array $attributes_defaults = [
    ];

    protected $auto_validate_on_construction = true;
}

/**
 * @property int total
 * @property int count
 * @property int per_page
 * @property int current_page
 * @property int total_pages
 */
class MetaPagination extends BasicBeanValidated
{
    protected array $attributes_details = [
        'total' => ['mandatory' => true, 'type' => 'int'],
        'count' => ['mandatory' => true, 'type' => 'int'],
        'per_page' => ['mandatory' => true, 'type' => 'int'],
        'current_page' => ['mandatory' => true, 'type' => 'int'],
        'total_pages' => ['mandatory' => true, 'type' => 'int'],
    ];

    protected array $attributes_defaults = [
        'total' => 0,
        'count' => 0,
        'per_page' => 0,
        'current_page' => 0,
        'total_pages' => 0,
    ];

    protected $auto_validate_on_construction = true;
}
