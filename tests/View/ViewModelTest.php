<?php

namespace TimMcLeod\LaravelCoreLib\Tests\View;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Orchestra\Testbench\TestCase;
use TimMcLeod\LaravelCoreLib\View\BaseViewModel;

class ViewModelTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // set up
    }

    public function tearDown()
    {
        // tear down

        parent::tearDown();
    }

    public function testGetData()
    {
        $vm = new ViewModelStub([
            'user'     => new UserStub(['name' => 'Marty']),
            'years'    => collect([1885, 1955, 1985]),
            'location' => 'Hill Valley'
        ]);

        $this->assertEquals(new UserStub(['name' => 'Marty']), $vm->user);
        $this->assertEquals('Hill Valley', $vm->location);
        $this->assertEquals(collect([1885, 1955, 1985]), $vm->years);

        $this->assertEquals(null, $vm->another_random_property);
    }

    public function testValidateData()
    {
        // Since the year is a string instead of an integer, an exception should be thrown.
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp("/Invalid data: /");

        new ViewModelWithRulesStub([
            'year'     => 'Alternate 1985',
            'location' => 'Hill Valley'
        ]);
    }

    public function testDataIsset()
    {
        $vm = new ViewModelStub([
            'user'     => new UserStub(['name' => 'Marty']),
            'years'    => collect([1885, 1955, 1985]),
            'location' => 'Hill Valley'
        ]);

        $this->assertTrue(isset($vm->user));
        $this->assertTrue(isset($vm->location));
        $this->assertTrue(isset($vm->years));

        $this->assertFalse(isset($vm->data));
        $this->assertFalse(isset($vm->another_random_property));
    }
}

class ViewModelStub extends BaseViewModel
{
}

class ViewModelWithRulesStub extends BaseViewModel
{
    protected $rules = [
        'year'     => 'integer',
        'location' => 'string'
    ];
}

class UserStub extends Model
{
}