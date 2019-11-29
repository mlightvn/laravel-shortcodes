# ShortCodes for Laravel

*A powerful alternative to view composers. Asynchronous shortcodes, reloadable shortcodes, console generator, caching - everything that you can imagine.*

## Installation

Run ```composer require namtenten/shortcodes```

Laravel >=5.5 uses Package Auto-Discovery, so you don't need to manually add the ServiceProvider and Facades

## Usage

Let's consider that we want to make a list of recent news and reuse it in several views.

First of all we can create a Shortcode class using the artisan command provided by the package.
```bash
php artisan make:shortcodes UsersList
```
This command generates two files:

1) `resources/views/shortcodes/recent_news.blade.php` is an empty view. 

Add "--plain" option if you do not need a view.

2) `app/ShortCodes/UsersList` is a shortcode class.

```php
<?php

namespace App\ShortCodes;

use NamTenTen\ShortCodes\AbstractShortCode;

class UsersList extends AbstractShortCode
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        //

        return view('shortcodes.recent_news', [
            'config' => $this->config,
        ]);
    }
}
```

> Note: You can use your own stubs if you need. Publish config file to change paths.

The last step is to call the shortcode.
There are several ways to do so.

```php
@shortcode('UsersList')
```
or
```php
{{ ShortCode::run('UsersList') }}
```
or even
```php
{{ ShortCode::UsersList() }}
```

There is no real difference between these. The choice is up to you.

## Passing variables to shortcode

### Via config array

Let's carry on with the "recent news" example.

Imagine that we usually need to show *five* news, but in some views we need to show *ten*.
This can be easily achieved by:

```php
class UsersList extends AbstractShortCode
{
    ...
    protected $config = [
        'count' => 5
    ];
    ...
}

...
@shortcode('UsersList') // shows 5
@shortcode('UsersList', ['count' => 10]) // shows 10
```
`['count' => 10]` is a config array that can be accessed by $this->config.

Config array is available in every shortcode method so you can use it to configure placeholder and container too (see below)

> Note: Config fields that are not specified when you call a shortcode aren't overridden:

```php
class UsersList extends AbstractShortCode
{
    ...
    protected $config = [
        'count' => 5,
        'foo'   => 'bar'
    ];
    
    ...
}

@shortcode('UsersList', ['count' => 10]) // $this->config['foo'] is still 'bar'
```

> Note 2: You may want (but you probably don't) to create your own BaseShortCode and inherit from it.
That's fine. The only edge case is merging config defaults from a parent and a child. 
In this case do the following:

1) Do not add `protected $config = [...]` line to a child.

2) Instead add defaults like this:

```php
public function __construct(array $config = [])
{
    $this->addConfigDefaults([
        'child_key' => 'bar'
    ]);

    parent::__construct($config);
}
```

### Directly

You can also choose to pass additional parameters to `run()` method directly.

```php
@shortcode('UsersList', ['count' => 10], 'date', 'asc')
...
public function run($sortBy, $sortOrder) { }
...
```

`run()` method is resolved via Service Container, so method injection is also available here.

## Namespaces

By default the package tries to find your shortcode in the ```App\ShortCodes``` namespace.

You can override this by publishing package config (```php artisan vendor:publish --provider="NamTenTen\ShortCodes\ServiceProvider"```) and setting `default_namespace` property.

Although using the default namespace is very convenient, in some cases you may wish to have more flexibility. 
For example, if you've got dozens of shortcodes it makes sense to group them in namespaced folders.

No problem, there are several ways to call those shortcodes:

1) Pass a full shortcode name from the `default_namespace` (basically `App\ShortCodes`) to the `run` method.
```php
@shortcode('News\UsersList', $config)
```

2) Use dot notation.
```php
@shortcode('news.UsersList', $config)
```

3) FQCN is also an option.
```php
@shortcode('\App\Http\Some\Namespace\ShortCode', $config)
```

## Asynchronous shortcodes

In some situations it can be very beneficial to load shortcode content with AJAX.

Fortunately, this can be achieved very easily!
All you need to do is to change facade or blade directive - `ShortCode::` => `AsyncShortCode::`, `@shortcode` => `@asyncShortCode`

ShortCode params are encrypted (by default) and sent via ajax call under the hood. So expect them to be `json_encoded()` and `json_decoded()` afterwards.

> Note: You can turn encryption off for a given shortcode by setting `public $encryptParams = false;` on it. However, this action will make shortcode params publicly accessible, so please make sure to not leave any vulnerabilities.
For example, if you pass something like user_id through shortcode params and turn encryption off, you do need to add one more access check inside the shortcode.

> Note: You can set `use_jquery_for_ajax_calls` to `true` in the config file to use it for ajax calls if you want to.

By default nothing is shown until ajax call is finished.

This can be customized by adding a `placeholder()` method to the shortcode class.

```php
public function placeholder()
{
    return 'Loading...';
}
```

> Side note: If you need to do something with the routes package used to load async shortcodes (e.g. you run app in a subfolder http://site.com/app/) you need to copy NamTenTen\ShortCodes\ServiceProvider to your app, modify it according to your needs and register it in Laravel instead of the former one.

## Reloadable shortcodes

You can go even further and automatically reload shortcode every N seconds.

Just set the `$reloadTimeout` property of the shortcode class and it is done.

```php
class UsersList extends AbstractShortCode
{
    /**
     * The number of seconds before each reload.
     *
     * @var int|float
     */
    public $reloadTimeout = 10;
}
```

Both sync and async shortcodes can become reloadable.

You should use this feature with care, because it can easily spam your app with ajax calls if timeouts are too low.
Consider using web sockets too but they are way harder to set up.

## Container

Async and Reloadable shortcodes both require some DOM interaction so they wrap all shortcode output in a html container.
This container is defined by `AbstractShortCode::container()` method and can be customized too.

```php
/**
 * Async and reloadable shortcodes are wrapped in container.
 * You can customize it by overriding this method.
 *
 * @return array
 */
public function container()
{
    return [
        'element'       => 'div',
        'attributes'    => 'style="display:inline" class="namtenten-shortcode-container"',
    ];
}
```

> Note: Nested async or reloadable shortcodes are not supported.

## Caching

There is also a simple built-in way to cache entire shortcode output.
Just set $cacheTime property in your shortcode class and you are done.

```php
class UsersList extends AbstractShortCode
{
    /**
     * The number of minutes before cache expires.
     * False means no caching at all.
     *
     * @var int|float|bool
     */
    public $cacheTime = 60;
}
```

No caching is turned on by default.
A cache key depends on a shortcode name and each shortcode parameter.
Override ```cacheKey``` method if you need to adjust it.

### Cache tagging

When tagging is supported ([see the Laravel cache documentation](https://laravel.com/docs/cache#cache-tags)) and to 
simplify cache flushing, a tag `shortcodes` is assigned by default to all shortcodes. 
You can define one or more additional tags to your shortcodes by setting the values 
in the `$cacheTags` property in your shortcode class. Example :

```php
class UsersList extends AbstractShortCode
{
    /**
     * Cache tags allow you to tag related items in the cache 
     * and then flush all cached values that assigned a given tag.
     *
     * @var array
     */
    public $cacheTags = ['news', 'frontend'];
}
```

For this example, if you need to flush :

```php
// Clear shortcodes with the tag news
Cache::tags('news')->flush();

// Clear shortcodes with the tag news OR backend
Cache::tags(['news', 'frontend'])->flush();

// Flush all shortcodes cache
Cache::tags('shortcodes')->flush();
```

## ShortCode groups (extra)

In most cases Blade is a perfect tool for setting the position and order of shortcodes.
However, sometimes you may find useful the following approach:

```php
// add several shortcodes to the 'sidebar' group anywhere you want (even in controller)
ShortCode::group('sidebar')->position(5)->addShortCode('shortcodeName1', $config1);
ShortCode::group('sidebar')->position(4)->addAsyncShortCode('shortcodeName2', $config2);

// display them in a view in the correct order
@shortcodeGroup('sidebar')
// or 
{{ ShortCode::group('sidebar')->display() }}
```

`position()` can be omitted from the chain.

`ShortCode::group('sidebar')->addShortCode('files');` 

equals

`ShortCode::group('sidebar')->position(100)->addShortCode('files');`

You can set a separator that will be display between shortcodes in a group.
`ShortCode::group('sidebar')->setSeparator('<hr>')->...;`

You can also wrap each shortcode in a group using `wrap` method like that.
```php
ShortCode::group('sidebar')->wrap(function ($content, $index, $total) {
    // $total is a total number of shortcodes in a group.
    return "<div class='shortcode-{$index}'>{$content}</div>";
})->...;
```

### Removing shortcodes from a group

There is a couple of ways to remove shortcode/shortcodes from a group after they've been already added.

1) Remove one shortcode by its unique `id`
```php
$id1 = ShortCode::group('sidebar')->addShortCode('files');
$id2 = ShortCode::group('sidebar')->addAsyncShortCode('files');
ShortCode::group('sidebar')->removeById($id1); // There is only second shortcode in the group now
```

2) Remove all shortcodes with specific name
```php
ShortCode::group('sidebar')->addShortCode('files');
ShortCode::group('sidebar')->addAsyncShortCode('files');
ShortCode::group('sidebar')->removeByName('files'); // ShortCode group is empty now
```

3) Remove all shortcodes that are placed on a specific position.
```php
ShortCode::group('sidebar')->position(42)->addShortCode('files');
ShortCode::group('sidebar')->position(42)->addAsyncShortCode('files');
ShortCode::group('sidebar')->removeByPosition(42); // ShortCode group is empty now
```

4) Remove all shortcodes at once.
```php
ShortCode::group('sidebar')->addShortCode('files');
ShortCode::group('sidebar')->addAsyncShortCode('files');
ShortCode::group('sidebar')->removeAll(); // ShortCode group is empty now
```

### Checking the state of a group

`ShortCode::group('sidebar')->isEmpty(); // bool`

`ShortCode::group('sidebar')->any(); // bool`

`ShortCode::group('sidebar')->count(); // int`

## Namespaces for third party packages (extra)

In some cases, it may be useful to deliver shortcodes with your own packages. For example, if your package allows 
you to manage news, it would be convenient to have immediately configurable shortcodes, ready for display, directly 
delivered with your package.

To avoid having to use the fqcn each time, you can set a shortcode namespace into your package provider. This way the 
shortcodes from your package can be more easily identified, and especially the syntax will be shorter.

To do that, all you have to do is to register the namespace in your package service provider :

```php
public function boot() 
{
    app('namtenten.shortcode-namespaces')->registerNamespace('my-package-name', '\VendorName\PackageName\Path\To\ShortCodes');
}
```

After that you can use the namespace in your views :

```php
@shortcode('my-package-name::foo.bar')

// is equivalent to
@shortcode('\VendorName\PackageName\Path\To\ShortCodes\Foo\Bar')
```
 