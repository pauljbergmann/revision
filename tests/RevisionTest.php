<?php

namespace Stevebauman\Revision\Tests;

use Stevebauman\Revision\Tests\Stubs\Models\Revision;
use Stevebauman\Revision\Tests\Stubs\Models\User;
use Stevebauman\Revision\Tests\Stubs\Models\Post;

class RevisionTest extends FunctionalTestCase
{
    public $user;

    public function setUp()
    {
        parent::setUp();

        $user = new User();

        $user->username = 'User One';
        $user->save();

        $this->user = $user;
    }

    public function testCreate()
    {
        $post = new Post();

        $post->title = 'Test';
        $post->description = 'Testing';
        $post->save();

        $revisions = Revision::all();

        $this->assertEquals(0, $revisions->count());
    }

    public function testModify()
    {
        $post = new Post();

        $post->title = 'Test';
        $post->description = 'Testing';
        $post->save();

        $post->title = 'Modified';
        $post->save();

        $revisions = Revision::all();
        $this->assertEquals(1, $revisions->count());

        $titleRevision = $revisions->get(0);

        $this->assertEquals('title', $titleRevision->key);
        $this->assertEquals('Test', $titleRevision->old_value);
        $this->assertEquals('Modified', $titleRevision->new_value);
    }

    public function testOnlyColumns()
    {
        $post = new Post();
        $post->setRevisionColumns(['title']);

        $post->title = 'Testing';
        $post->description = 'Testing';
        $post->save();

        $post->title = 'Changed';
        $post->save();

        $revisions = Revision::all();

        $this->assertEquals(1, $revisions->count());
        $this->assertEquals('title', $revisions->get(0)->key);
        $this->assertEquals('Changed', $revisions->get(0)->new_value);
        $this->assertEquals('Testing', $revisions->get(0)->old_value);
    }

    public function testAvoidColumns()
    {
        $post = new Post();

        $post->setRevisionColumnsToAvoid(['title']);
        $post->title = 'Testing';
        $post->description = 'Testing';
        $post->save();

        $post->title = 'New Title';
        $post->save();

        $this->assertEquals(0, Revision::all()->count());
    }

    public function testColumnFormatting()
    {
        $post = new Post();

        $post->title = 'Testing';
        $post->description = 'Testing';
        $post->save();

        $post->title = 'Changed';
        $post->description = 'Changed';
        $post->save();

        $revisions = $post->revisions;

        $this->assertEquals('Post Title', $revisions->get(0)->getColumnName());
        $this->assertEquals('Post Description', $revisions->get(1)->getColumnName());
    }

    public function testColumnMeans()
    {
        $post = new Post();

        $post->user_id = $this->user->id;
        $post->title = 'Testing';
        $post->description = 'Testing';
        $post->save();

        $user = new User();
        $user->username = 'User Two';
        $user->save();

        $post->user_id = $user->id;
        $post->save();

        $revisions = $post->revisions;

        $this->assertEquals('User Two', $revisions->get(0)->getNewValue());
    }

    public function testGetRevisionColumnsFormatted()
    {
        $post = new Post();

        $columns = [
            'id' => 'ID',
            'created_at' => 'Created',
            'updated_at' => 'Updated',
            'title' => 'Post Title',
            'description' => 'Post Description',
        ];

        $this->assertEquals($columns, $post->getRevisionColumnsFormatted());
    }

    public function testGetRevisionColumnsMean()
    {
        $post = new Post();

        $means = [
            'user_id' => 'user.username'
        ];

        $this->assertEquals($means, $post->getRevisionColumnsMean());
    }

    public function testGetUserResponsible()
    {
        $post = new Post();

        $post->title = 'Testing';
        $post->description = 'Testing';
        $post->save();

        $post->user_id = $this->user->id;
        $post->save();

        $revisions = $post->revisions;

        $user = $revisions->get(0)->getUserResponsible();

        $this->assertEquals(1, $user->id);
        $this->assertEquals('User One', $user->username);
    }
}
