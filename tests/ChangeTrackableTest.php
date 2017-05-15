<?php

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase;
use TimMcLeod\LaravelCoreLib\Models\Traits\ChangeTrackable;

class ChangeTrackableTest extends TestCase
{
    /**
     * @var ChangeTrackableTestUser
     */
    protected $user;

    /**
     * @var AgeTrackableTestUser
     */
    protected $user2;

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        $this->schema()->create('users', function (Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->string('email');
            $table->tinyInteger('age')->nullable();
            $table->boolean('plays_guitar');
        });

        ChangeTrackableTestUser::unguard();

        ChangeTrackableTestUser::create([
                'id'           => 1,
                'name'         => 'Marty',
                'email'        => 'martymc@thrashermagazine.com',
                'age'          => 50,
                'plays_guitar' => 1,
            ]
        );

        $this->user = ChangeTrackableTestUser::first();
        $this->user2 = AgeTrackableTestUser::first();
    }

    /**
     * Tear down the database schema.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->schema()->drop('users');

        parent::tearDown();
    }

    public function testNothingTracked()
    {
        // Verify that no changes are present
        $this->assertEquals([], $this->user->getTrackedChangesArray());
        $this->assertEquals([], $this->user->getTrackedChangesArrayFor(['email']));
        $this->assertEquals([], $this->user->getTrackedChangesArrayForAll());
        $this->assertEquals('', $this->user->getTrackedChanges());
        $this->assertEquals('', $this->user->getTrackedChangesFor(['email']));

        $this->user->save();

        // Verify that no changes are present after save
        $this->assertEquals([], $this->user->getTrackedChangesArray());
        $this->assertEquals([], $this->user->getTrackedChangesArrayFor(['email']));
        $this->assertEquals([], $this->user->getTrackedChangesArrayForAll());
        $this->assertEquals('', $this->user->getTrackedChanges());
        $this->assertEquals('', $this->user->getTrackedChangesFor(['email']));
    }

    public function testGetTrackedChangesArray()
    {
        // Change user's name
        $this->user->name = 'Calvin Klein';

        // Save hasn't happened yet, so expect no tracked changes
        $this->assertEquals([], $this->user->getTrackedChangesArray());

        $this->user->save();

        // Name has changed after a save event, so expect to see those changes
        $this->assertEquals([
            'name' => ['old' => 'Marty', 'new' => 'Calvin Klein']
        ], $this->user->getTrackedChangesArray());

        // Change user's age
        $this->user->age = 51;

        // Save hasn't happened yet, so expect no new tracked changes
        $this->assertEquals([
            'name' => ['old' => 'Marty', 'new' => 'Calvin Klein']
        ], $this->user->getTrackedChangesArray());

        $this->user->save();

        // Age has changed after a save event, so expect to see those changes
        $this->assertEquals([
            'age'  => ['old' => 50, 'new' => 51],
            'name' => ['old' => 'Marty', 'new' => 'Calvin Klein']
        ], $this->user->getTrackedChangesArray());
    }

    public function testAgeTrackedGetTrackedChangesArray()
    {
        // Change user's name
        $this->user2->name = 'Calvin Klein';

        // Save hasn't happened yet, so expect no tracked changes
        $this->assertEquals([], $this->user2->getTrackedChangesArray());

        $this->user2->save();

        // Name is not in trackable array, so expect no new tracked changes
        $this->assertEquals([], $this->user2->getTrackedChangesArray());

        // Change user's age
        $this->user2->age = 51;

        // Save hasn't happened yet, so expect no new tracked changes
        $this->assertEquals([], $this->user2->getTrackedChangesArray());

        $this->user2->save();

        // Age has changed after a save event, so expect to see those changes
        $this->assertEquals([
            'age'  => ['old' => 50, 'new' => 51],
        ], $this->user2->getTrackedChangesArray());
    }

    /**
     * Get a schema builder instance.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function schema()
    {
        return $this->getConnection()->getSchemaBuilder();
    }
}

/**
 * Class ChangeTrackableTestUser
 *
 * @property integer $id
 * @property string  $name
 * @property string  $email
 * @property integer $age
 * @property boolean $plays_guitar
 */
class ChangeTrackableTestUser extends Eloquent
{
    use ChangeTrackable;

    protected $table = 'users';

    protected $casts = [
        'id'           => 'integer',
        'name'         => 'string',
        'email'        => 'string',
        'age'          => 'integer',
        'plays_guitar' => 'boolean',
    ];
}

class AgeTrackableTestUser extends ChangeTrackableTestUser
{
    protected $trackable = ['age'];
}
