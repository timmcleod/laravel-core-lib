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

    public function testGetTrackedChangesArrayInAgeTrackedModel()
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
            'age' => ['old' => 50, 'new' => 51],
        ], $this->user2->getTrackedChangesArray());
    }

    public function testGetTrackedChangesArrayFor()
    {
        // Change user's name
        $this->user->name = 'Calvin Klein';
        $this->user->save();

        // Name has changed after a save event, so expect to see those changes
        $this->assertEquals([
            'name' => ['old' => 'Marty', 'new' => 'Calvin Klein']
        ], $this->user->getTrackedChangesArrayFor(['name']));

        // Expect to see all changes when passing empty array for attributes param
        $this->assertEquals([
            'name' => ['old' => 'Marty', 'new' => 'Calvin Klein']
        ], $this->user->getTrackedChangesArrayFor([]));

        // Expect to see no changes returned when asking for changes for 'age' attribute
        $this->assertEquals([], $this->user->getTrackedChangesArrayFor(['age']));

        // Change user's age
        $this->user->age = 51;
        $this->user->save();

        // Expect to see all changes when passing empty array for attributes param
        $this->assertEquals([
            'age'  => ['old' => 50, 'new' => 51],
            'name' => ['old' => 'Marty', 'new' => 'Calvin Klein']
        ], $this->user->getTrackedChangesArrayFor([]));

        // Expect to see some changes returned when asking for changes for 'age' attribute
        $this->assertEquals([
            'age' => ['old' => 50, 'new' => 51]
        ], $this->user->getTrackedChangesArrayFor(['age']));
    }

    public function testGetTrackedChangesArrayForInAgeTrackedModel()
    {
        // The getTrackedChangesArrayFor() method should behave exactly the same
        // regardless of whether or not the $trackable property is defined, so
        // these assertions are exactly the same as the ones in the last test
        // except that these are using $user2, which has $trackable defined

        // Change user's name
        $this->user2->name = 'Calvin Klein';
        $this->user2->save();

        // Name has changed after a save event, so expect to see those changes
        $this->assertEquals([
            'name' => ['old' => 'Marty', 'new' => 'Calvin Klein']
        ], $this->user2->getTrackedChangesArrayFor(['name']));

        // Expect to see all changes when passing empty array for attributes param
        $this->assertEquals([
            'name' => ['old' => 'Marty', 'new' => 'Calvin Klein']
        ], $this->user2->getTrackedChangesArrayFor([]));

        // Expect to see no changes returned when asking for changes for 'age' attribute
        $this->assertEquals([], $this->user2->getTrackedChangesArrayFor(['age']));

        // Change user's age
        $this->user2->age = 51;
        $this->user2->save();

        // Expect to see all changes when passing empty array for attributes param
        $this->assertEquals([
            'age'  => ['old' => 50, 'new' => 51],
            'name' => ['old' => 'Marty', 'new' => 'Calvin Klein']
        ], $this->user2->getTrackedChangesArrayFor([]));

        // Expect to see some changes returned when asking for changes for 'age' attribute
        $this->assertEquals([
            'age' => ['old' => 50, 'new' => 51]
        ], $this->user2->getTrackedChangesArrayFor(['age']));
    }

    public function testGetTrackedChangesArrayForAll()
    {
        // The getTrackedChangesArrayForAll() method should always return
        // all of the changes that have been tracked, regardless of
        // whether or not $trackable is defined on the model.

        // Change user's name
        $this->user->name = 'Calvin Klein';
        $this->user->save();

        // Name has changed after a save event, so expect to see those changes
        $this->assertEquals([
            'name' => ['old' => 'Marty', 'new' => 'Calvin Klein']
        ], $this->user->getTrackedChangesArrayForAll());

        // Change user's age
        $this->user->age = 51;
        $this->user->save();

        // Expect to see all changes when passing empty array for attributes param
        $this->assertEquals([
            'age'  => ['old' => 50, 'new' => 51],
            'name' => ['old' => 'Marty', 'new' => 'Calvin Klein']
        ], $this->user->getTrackedChangesArrayForAll());
    }

    public function testGetTrackedChangesArrayForAllInAgeTrackedModel()
    {
        // The getTrackedChangesArrayForAll() method should always return
        // all of the changes that have been tracked, regardless of
        // whether or not $trackable is defined on the model.

        // Change user's name
        $this->user2->name = 'Calvin Klein';
        $this->user2->save();

        // Name has changed after a save event, so expect to see those changes
        $this->assertEquals([
            'name' => ['old' => 'Marty', 'new' => 'Calvin Klein']
        ], $this->user2->getTrackedChangesArrayForAll());

        // Change user's age
        $this->user2->age = 51;
        $this->user2->save();

        // Expect to see all changes when passing empty array for attributes param
        $this->assertEquals([
            'age'  => ['old' => 50, 'new' => 51],
            'name' => ['old' => 'Marty', 'new' => 'Calvin Klein']
        ], $this->user2->getTrackedChangesArrayForAll());
    }

    public function testHasTrackedChanges()
    {
        // Change user's name
        $this->user->name = 'Calvin Klein';

        // Save hasn't happened yet, so expect no tracked changes
        $this->assertEquals(false, $this->user->hasTrackedChanges());

        $this->user->save();

        // Name has changed after a save event, so expect to see those changes
        $this->assertEquals(true, $this->user->hasTrackedChanges());

        // Change user's age
        $this->user->age = 51;
        $this->user->save();

        // Age has changed after a save event, so expect to see those changes
        $this->assertEquals(true, $this->user->hasTrackedChanges());
    }

    public function testHasTrackedChangesInAgeTrackedModel()
    {
        // Change user's name
        $this->user2->name = 'Calvin Klein';
        $this->user2->save();

        // We shouldn't see any changes since `name` isn't marked as trackable.
        $this->assertEquals(false, $this->user2->hasTrackedChanges());

        // Change user's age
        $this->user2->age = 51;
        $this->user2->save();

        // Age has changed after a save event, so expect to see those changes
        $this->assertEquals(true, $this->user2->hasTrackedChanges());
    }

    public function testHasAnyTrackedChanges()
    {
        // No changes have been made yet, so nothing should be tracked
        $this->assertEquals(false, $this->user->hasAnyTrackedChanges());

        // Change user's name
        $this->user->name = 'Calvin Klein';

        // Save hasn't happened yet, so expect no tracked changes
        $this->assertEquals(false, $this->user->hasAnyTrackedChanges());

        $this->user->save();

        // Name has changed after a save event, so expect to see those changes
        $this->assertEquals(true, $this->user->hasAnyTrackedChanges());

        // Change user's age
        $this->user->age = 51;
        $this->user->save();

        // Age has changed after a save event, so expect to see those changes
        $this->assertEquals(true, $this->user->hasAnyTrackedChanges());
    }

    public function testHasAnyTrackedChangesInAgeTrackedModel()
    {
        // No changes have been made yet, so nothing should be tracked
        $this->assertEquals(false, $this->user2->hasAnyTrackedChanges());

        // Change user's name
        $this->user2->name = 'Calvin Klein';
        $this->user2->save();

        // We should see changes since this method doesn't consider what is $trackable
        $this->assertEquals(true, $this->user2->hasAnyTrackedChanges());

        // Change user's age
        $this->user2->age = 51;
        $this->user2->save();

        // Age has changed after a save event, so expect to see those changes
        $this->assertEquals(true, $this->user2->hasAnyTrackedChanges());
    }

    public function testHasAnyTrackedChangesFor()
    {
        // No changes have been made yet, so nothing should be tracked
        $this->assertEquals(false, $this->user->hasAnyTrackedChangesFor(['name']));

        // Change user's name
        $this->user->name = 'Calvin Klein';
        $this->user->save();

        // Name has changed after a save event, so expect to see those changes
        $this->assertEquals(true, $this->user->hasAnyTrackedChangesFor(['name']));

        // Expect to see NO changes when passing empty array for attributes param
        $this->assertEquals(false, $this->user->hasAnyTrackedChangesFor([]));

        // Expect to see no changes returned when asking for changes for 'age' attribute
        $this->assertEquals(false, $this->user->hasAnyTrackedChangesFor(['age']));

        // Expect to see changes for either name or age since name has changed
        $this->assertEquals(true, $this->user->hasAnyTrackedChangesFor(['name', 'age']));

        // Change user's age
        $this->user->age = 51;
        $this->user->save();

        // Expect to see changes for age after save
        $this->assertEquals(true, $this->user->hasAnyTrackedChangesFor(['age']));

        // Expect to see changes for either name or age
        $this->assertEquals(true, $this->user->hasAnyTrackedChangesFor(['name', 'age']));

        // Expect to see NO changes when passing empty array for attributes param
        $this->assertEquals(false, $this->user->hasAnyTrackedChangesFor([]));
    }
    
    public function testHasAnyTrackedChangesForInAgeTrackedModel()
    {
        // The hasAnyTrackedChangesFor() method should behave exactly the same
        // regardless of whether or not the $trackable property is defined, so
        // these assertions are exactly the same as the ones in the last test
        // except that these are using $user2, which has $trackable defined

        // No changes have been made yet, so nothing should be tracked
        $this->assertEquals(false, $this->user2->hasAnyTrackedChangesFor(['name']));

        // Change user's name
        $this->user2->name = 'Calvin Klein';
        $this->user2->save();

        // Name has changed after a save event, so expect to see those changes
        $this->assertEquals(true, $this->user2->hasAnyTrackedChangesFor(['name']));

        // Expect to see NO changes when passing empty array for attributes param
        $this->assertEquals(false, $this->user2->hasAnyTrackedChangesFor([]));

        // Expect to see NO changes returned when asking for changes for 'age' attribute
        $this->assertEquals(false, $this->user2->hasAnyTrackedChangesFor(['age']));

        // Expect to see changes for either name or age since name has changed
        $this->assertEquals(true, $this->user2->hasAnyTrackedChangesFor(['name', 'age']));

        // Change user's age
        $this->user2->age = 51;
        $this->user2->save();

        // Expect to see changes for age after save
        $this->assertEquals(true, $this->user2->hasAnyTrackedChangesFor(['age']));

        // Expect to see changes for either name or age
        $this->assertEquals(true, $this->user2->hasAnyTrackedChangesFor(['name', 'age']));

        // Expect to see NO changes when passing empty array for attributes param
        $this->assertEquals(false, $this->user2->hasAnyTrackedChangesFor([]));
    }

    public function testGetTrackedChanges()
    {
        // Change user's name
        $this->user->name = 'Calvin Klein';

        // Save hasn't happened yet, so expect no tracked changes
        $this->assertEquals('', $this->user->getTrackedChanges());

        $this->user->save();

        // Name has changed after a save event, so expect to see those changes
        $this->assertEquals('name: Marty > Calvin Klein', $this->user->getTrackedChanges());

        // Change the format of those changes:
        $this->assertEquals('name was changed from "Marty" to "Calvin Klein"',
            $this->user->getTrackedChanges('{attribute} was changed from "{old}" to "{new}"'));

        // Change user's age
        $this->user->age = 51;

        // Save hasn't happened yet, so expect no new tracked changes
        $this->assertEquals('name: Marty > Calvin Klein', $this->user->getTrackedChanges());

        $this->user->save();

        // Age has changed after a save event, so expect to see those changes
        $this->assertEquals('age: 50 > 51 | name: Marty > Calvin Klein', $this->user->getTrackedChanges());

        // Change the format and delimiter of those changes:
        $this->assertEquals(
            'age was changed from "50" to "51"' . PHP_EOL .
            'name was changed from "Marty" to "Calvin Klein"',
            $this->user->getTrackedChanges('{attribute} was changed from "{old}" to "{new}"', PHP_EOL));

        // Change the ID
        $this->user->id = 1000;
        $this->user->save();

        $this->assertEquals(
            'age was changed from "50" to "51"' . PHP_EOL .
            'id was changed from "1" to "1000"' . PHP_EOL .
            'name was changed from "Marty" to "Calvin Klein"',
            $this->user->getTrackedChanges('{attribute} was changed from "{old}" to "{new}"', PHP_EOL));

        // Change the age to null
        $this->user->age = null;
        $this->user->save();

        $this->assertEquals(
            'age was changed from "50" to "zilch"' . PHP_EOL .
            'id was changed from "1" to "1000"' . PHP_EOL .
            'name was changed from "Marty" to "Calvin Klein"',
            $this->user->getTrackedChanges('{attribute} was changed from "{old}" to "{new}"', PHP_EOL, 'nada', 'zilch'));
    }

    public function testGetTrackedChangesInAgeTrackedModel()
    {
        // Change user's name
        $this->user2->name = 'Calvin Klein';

        // Save hasn't happened yet, so expect no tracked changes
        $this->assertEquals('', $this->user2->getTrackedChanges());

        $this->user2->save();

        // Name is not in trackable array, so expect NO new tracked changes
        $this->assertEquals('', $this->user2->getTrackedChanges());

        // Change user's age
        $this->user2->age = 51;

        // Save hasn't happened yet, so expect no new tracked changes
        $this->assertEquals('', $this->user2->getTrackedChanges());

        $this->user2->save();

        // Age has changed after a save event, so expect to see those changes
        $this->assertEquals('age: 50 > 51', $this->user2->getTrackedChanges());
    }

    public function testGetTrackedChangesForAll()
    {
        // Change user's name
        $this->user->name = 'Calvin Klein';

        // Save hasn't happened yet, so expect no tracked changes
        $this->assertEquals('', $this->user->getTrackedChangesForAll());

        $this->user->save();

        // Name has changed after a save event, so expect to see those changes
        $this->assertEquals('name: Marty > Calvin Klein', $this->user->getTrackedChangesForAll());

        // Change the format of those changes:
        $this->assertEquals('name was changed from "Marty" to "Calvin Klein"',
            $this->user->getTrackedChangesForAll('{attribute} was changed from "{old}" to "{new}"'));

        // Change user's age
        $this->user->age = 51;

        // Save hasn't happened yet, so expect no new tracked changes
        $this->assertEquals('name: Marty > Calvin Klein', $this->user->getTrackedChangesForAll());

        $this->user->save();

        // Age has changed after a save event, so expect to see those changes too
        $this->assertEquals('age: 50 > 51 | name: Marty > Calvin Klein', $this->user->getTrackedChangesForAll());

        // Change the format and delimiter of those changes:
        $this->assertEquals(
            'age was changed from "50" to "51"' . PHP_EOL .
            'name was changed from "Marty" to "Calvin Klein"',
            $this->user->getTrackedChangesForAll('{attribute} was changed from "{old}" to "{new}"', PHP_EOL));
    }

    public function testGetTrackedChangesForAllInAgeTrackedModel()
    {
        // Change user's name
        $this->user2->name = 'Calvin Klein';

        // Save hasn't happened yet, so expect no tracked changes
        $this->assertEquals('', $this->user2->getTrackedChangesForAll());

        $this->user2->save();

        // Name is not in trackable array, but we should still see the changes
        $this->assertEquals('name: Marty > Calvin Klein', $this->user2->getTrackedChangesForAll());

        // Change user's age
        $this->user2->age = 51;

        // Save hasn't happened yet, so expect no new tracked changes
        $this->assertEquals('name: Marty > Calvin Klein', $this->user2->getTrackedChangesForAll());

        $this->user2->save();

        // Age has changed after a save event, so expect to see those changes
        $this->assertEquals('age: 50 > 51 | name: Marty > Calvin Klein', $this->user2->getTrackedChangesForAll());
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
