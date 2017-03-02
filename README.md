## Revision

[![Travis CI](https://img.shields.io/travis/stevebauman/revision.svg?style=flat-square)](https://travis-ci.org/stevebauman/revision)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/stevebauman/revision.svg?style=flat-square)](https://scrutinizer-ci.com/g/stevebauman/revision/?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/stevebauman/revision.svg?style=flat-square)](https://packagist.org/packages/stevebauman/revision)
[![Total Downloads](https://img.shields.io/packagist/dt/stevebauman/revision.svg?style=flat-square)](https://packagist.org/packages/stevebauman/revision)
[![License](https://img.shields.io/packagist/l/stevebauman/revision.svg?style=flat-square)](https://packagist.org/packages/stevebauman/revision)

Easily track any changes to an eloquent model.

### Installation

Insert Revision in your `composer.json` file:

```json
"stevebauman/revision": "1.2.*"
```

Now run `composer update`. Once that's complete, insert the Revision service provider inside your `config/app.php` file:

```php
Stevebauman\Revision\RevisionServiceProvider::class
```

Run `php vendor:publish` to publish the Revision migration file. Then, run `php artisan migrate`.

You're all set!

### Setup

Create the `Revision` model and insert the `belongsTo()` or `hasOne()` `user()` relationship as well as the `RevisionTrait`:

```php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Stevebauman\Revision\Traits\RevisionTrait;

class Revision extends Model
{
    use RevisionTrait;
    
    /**
     * The revisions table.
     *
     * @var string
     */
    protected $table = 'revisions';
    
    /**
     * The belongs to user relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(App\User::class);
    }
}
```

Insert the `Stevebauman\Revision\Traits\HasRevisionsTrait` onto your
model that you'd like to track changes on:

```php
namespace App;

use Stevebauman\Revision\Traits\HasRevisionsTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, HasRevisionsTrait;
    
    /**
     * The morphMany revisions relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function revisions()
    {
        return $this->morphMany(App\Revision::class, 'revisionable');
    }
    
    /**
     * The current users ID for storage in revisions.
     *
     * @return int|string
     */
    public function revisionUserId()
    {
        return auth()->id();
    }
}
```

### Usage

#### Revision Columns

You **must** insert the `$revisionColumns` property on your model to track revisions.

###### Tracking All Columns

To track all changes on every column on the models database table, use an asterisk like so:

```php
use Stevebauman\Revision\Traits\HasRevisionsTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, HasRevisionsTrait;
    
    /**
     * The columns to keep revisions of.
     *
     * @var array
     */
    protected $revisionColumns = ['*'];
}
```

###### Tracking Specific Columns

To track changes on specific columns, insert the column names you'd like to track like so:

```php
class User extends Authenticatable
{
    use Notifiable, HasRevisionsTrait;
    
    /**
     * The columns to keep revisions of.
     *
     * @var array
     */
    protected $revisionColumns = [
        'user_id',
        'title', 
        'description',
    ];
}
```

#### Displaying Revisions

To display your revisions on a record, call the relationship accessor `revisions`. Remember, this is just
a regular Laravel relationship, so you can eager load / lazy load your revisions as you please:

```php
$post = User::with('revisions')->find(1);

return view('user.show', ['user' => $user]);
```

On each revision record, you can use the following methods to display the revised data:

###### getColumnName()

To display the column name that the revision took place on, use the method `getColumnName()`:

```php
$revision = Revision::find(1);

echo $revision->getColumnName(); // Returns string
```

###### getUserResponsible()

To retrieve the User that performed the revision, use the method `getUserResponsible()`:

```php
$revision = Revision::find(1);

$user = $revision->getUserResponsible(); // Returns user model

echo $user->id;
echo $user->email;
echo $user->first_name;
```

###### getOldValue()

To retrieve the old value of the record, use the method `getOldValue()`:

```php
$revision = Revision::find(1);

echo $revision->getOldValue(); // Returns string
```

###### getNewValue()

To retrieve the new value of the record, use the method `getNewValue()`:

```php
$revision = Revision::find(1);

echo $revision->getNewValue(); // Returns string
```

###### Example

```html
// In your `post.show` view:

@if($post->revisions->count() > 0)
    
     <table class="table table-striped">
     
        <thead>
            <tr>
                <th>User Responsible</th>
                <th>Changed</th>
                <th>From</th>
                <th>To</th>
                <th>On</th>
            </tr>
        </thead>
        
        <tbody>
        
        @foreach($post->revisions as $revision)
        
            <tr>
            
                <td>
                    {{ $revision->getUserResponsible()->first_name }}
                    
                    {{ $record->getUserResponsible()->last_name }}
                </td>
                
                <td>{{ $revision->getColumnName() }}</td>
                
                <td>
                    @if(is_null($revision->getOldValue()))
                        <em>None</em>
                    @else
                        {{ $revision->getOldValue() }}
                    @endif
                </td>
                
                <td>{{ $revision->getNewValue() }}</td>
                
                <td>{{ $revision->created_at }}</td>
                
            </tr>
            
        @endforeach
        
        </tbody>
            
    </table>
@else
    <h5>There are no revisions to display.</h5>
@endif
```

#### Modifying the display of column names

To change the display of your column name that has been revised, insert the property `$revisionColumnsFormatted` on your model:

```php
/**
 * The formatted revised column names.
 *
 * @var array
 */
protected $revisionColumnsFormatted = [
    'user_id' => 'User',
    'title' => 'Post Title',
    'description' => 'Post Description',
];
```

#### Modifying the display of values

To change the display of your values that have been revised, insert the property `$revisionColumnsMean`. You can use
dot notation syntax to indicate relationship values. For example:

```php
/**
 * The formatted revised column names.
 *
 * @var array
 */
protected $revisionColumnsMean = [
    'user_id' => 'user.full_name',
];
```

You can even use laravel accessors with the `revisionColumnsMean` property.

> **Note**: The revised value will be passed into the first parameter of the accessor.

```php
protected $revisionColumnsMean = [
    'status' => 'status_label',
];

public function getStatusLabelAttribute($status = null)
{
    if(! $status) {
        $status = $this->getAttribute('status');
    }
    
    return view('status.label', ['status' => $status])->render();
}
```
