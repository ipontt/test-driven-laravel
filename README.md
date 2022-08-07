## About this repo

This repo follows Adam Wathan's test-driven Laravel tutorial using Laravel 9.x and [PEST](https://pestphp.com/). Installation instructions don't differ from a normal Laravel installation.

### Installation instructions

 1. `composer install`
 2. `npm install`
 3. `npm run build`
 4. `cp .env.example .env`
 5. Fill out `.env` file.

### Requirements

 - PHP 8.1 or above.
 - A database. PostgreSQL was used in development.
 - Stripe keys. You can obtain testing keys for free at [https://stripe.com/](https://stripe.com/).

### Running tests

Dev dependencies must be installed to run tests. Tests can be run by calling the pest binary.

```sh
./vendor/bin/pest
```

## Thoughts

These are the thoughts I have on every chapter of the course. I started writing these with Chapter 12, so the earliest chapters are not as accurate.

### Chapter 1

*No comment*

### Chapter 2

This chapter was confusing at first. It required some extra reading into Stripe's documentation and some experimenting. The tutorial uses Stripe's checkout.js, which is considered legacy at the time of writing.

### Chapter 3

This chapter contains some refactoring and I got the impression there was an attempt to hide logic for it's own sake. It makes me wonder if in the long run, it's better to hide away every bit of query builder logic behind model methods or query scopes to make the code more readable for someone who isn't familiar with the framework instead of just writing the queries plainly which may not be a pretty one liner, but is easy to understand if you have experience with the framework.

### Chapter 4

*No comment*

### Chapter 5

I like the domain object approach over Services or Action classes.

### Chapter 6

Facing a similar problem on a past project, my first instinct was to reach for database locks. The callback base solution proposed by this chapter seems much simpler

### Chapter 7

Mockery spies offer some neat syntax.

### Chapter 8

*No comment*

### Chapter 9

Same as in chapter 2. I tried implementing the component in alpinejs without using the inline notation. I'm not completely satisfied with the result but for the purposes of this project, it works. I want to look into switching Stripe's checkout.js for Stripe Elements later.

Ideally, I'd want to use Stripe Checkout and forgo the need for a javascript component completely.

### Chapter 10

Contract tests posed an interesting challenge with Pest. Traits didn't really work with Pest so instead, implementing the tests on a separate file and requiring it directly in each implementation worked well.

### Chapter 11

*No comment*

### Chapter 12

Maybe if the user doesn't immediately print the tickets, they could be sent by email as well.
Confirmation numbers are less guessable than autoincrementing ids, but they are still guessable to a lesser extent. Since no two confirmation numbers should be the same, maybe using an UUID would do the trick. Though again, I think the best approach is to send the tickets directly via email. Adding a finder seems like extra complexity if it's just to do the same thing route model binding can do at the moment by specifying keys. Asserting against a semantic html5 element is a great idea.

### Chapter 13

`Str::uuid()` generates the kind of random confirmation_numbers sought using the underlying `ramsey/uuid` package, so there's no need to make a custom implementation. It's also easy to test and plays nicely with PostgreSQL's UUID data type.

### Chapter 14

Considering readonly properties and constructor property promotion are now available, instead of passing a map to the constructor and using getters, we could pass each property as named arguments and directly access the public properties

### Chapter 15

Mockery spies are great for ensuring individual tests don't have too much responsabilities. About using a ticket's code to check if it's claimed or not; I think using order_id would do the same thing. Considering the ticket's code is computed from its it, making it an Attribute would save some trouble. A possible implementation could be
```php
use Hashid\Hashid;

public function getCodeAttribute()
{
    return $this->order_id
        ? (new Hashid(...))->encode($this->id)
        : null;
}
```
Also, the app key could be reused for the hashid salt.

### Chapter 16

Currently (laravel 9.x), there is no need to create additional helpers to assert again a Mailable's contents. `$mailable->assertSeeInHtml()` and `$mailable->assertSeeInText()` provide that functionality.

### Chapter 17

Dusk can be flimsy at times. It is a powerful tool though. Sometimes it breaks because of a chrome update. The appropriate chrome driver can be installed again with `php artisan dusk:chrome-driver`

### Chapter 18

Currently, Form Requests allows for some neat encapsulation of the validation logic. With some pre-validation modifications, we can just validate the fields we actually care about.
```php

class StoreConcertRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'date' => vsprintf(format: '%s %s', values: [$this->date, $this->time]),
            'ticket_price' => (int) ((float) $this->ticket_price * 100),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required'],
            ...
        ];
    }
}
```
and then pass the validated data directly to the model
```php
$concert = Auth::user()
    ->concerts()
    ->create(attributes: $request->safe()->except('ticket_quantity'))
    ->addTickets(quantity: $request->validated('ticket_quantity'))
    ->publish();
```

### Chapter 19

Currently, the `assertView*` function family can be used to assert against the response's original content. `assertViewIs`, `assertViewHas`, `assertViewHasAll`.
```php
$response
    ->assertStatus(200)
    ->assertViewHas('concerts', function ($view_concerts) use ($concerts) {
        return $view_concerts->contains($concerts[0])
            && $view_concerts->contains($concerts[1])
            && $view_concerts->contains($concerts[2]);
    });
```

With Pest, there are no view related helpers so accessing the response's original content seems to be the better way. Using `getData()` isn't really needed since the `Response` class implements a magic getter to directly access its data.

```php
expect($response)
    ->status()->toEqual(200)
    ->and($response->original->concerts)
        ->contains($concerts->get(0))->toBeTrue()
        ->contains($concerts->get(1))->toBeTrue()
        ->contains($concerts->get(2))->toBeTrue();
```
We can using factory sequences to avoid grouping concerts together.
```php
[$user, $otherUser] = User::factory()->count(2)->create();

$concerts = Concert::factory()
    ->count(4)
    ->sequence(
        ['user_id' => $user->id],
        ['user_id' => $user->id],
        ['user_id' => $otherUser->id],
        ['user_id' => $user->id],
    )
    ->create();
```
Even if `toBeTrue` and `toBeFalse` both exist as expectations, sometimes using opposite expectations with `->not->` make the test seem clearer

```php
expect($response->original->concerts)
    ->contains($concerts->get(0))->toBeTrue()
    ->contains($concerts->get(1))->toBeTrue()
    ->contains($concerts->get(2))->toBeFalse();
    ->contains($concerts->get(3))->toBeTrue();
// vs
expect($response->original->concerts)
    ->contains($concerts->get(0))->toBeTrue()
    ->contains($concerts->get(1))->toBeTrue()
    ->contains($concerts->get(2))->not->toBeTrue();
    ->contains($concerts->get(3))->toBeTrue();
```

### Chapter 20

Using explicit route model binding and form requests, some logic can be offloaded from the controller in particular for validation purposes.

### Chapter 21

Model factories allow for a lot of relationship logic, but it can get pretty verbose.
```php
Concert::factory()->has(Ticket::factory()->count(3))->create();
```
Coupling the `ticket_quantity` attribute to the `published` state can be done however
```php
public function published(?int $ticket_quantity = null): static
{
    $ticket_quantity ??= $this->faker->numberBetween(5, 20);

    return $this->has(Ticket::factory()->count($ticket_quantity))->state(fn (array $attributes): array => [
        'ticket_quantity' => $ticket_quantity,
        'published_at' => $this->faker->dateTimeBetween('+1 week', '+2 weeks'),
    ]);
}
```
```php
Concert::factory()->published(3)->create();
```

### Chapter 22

There is a very good collection method for dividing a collection in 2 based on a condition. And it can be used with higher order messages.
```php
// Instead of
$published_concerts = Auth::user()->concerts->filter->isPublished();
$unpublished_concerts = Auth::user()->concerts->reject->isPublished();

// One liner
[$published_concerts, $unpublished_concerts] = Auth::user()->concerts->partition->isPublished();
```

### Chapter 23

An issue with methods such as `ticketsRemaining()`, `ticketsSold()` and `totalTickets()`, is they query the database every time they're used. 

 - `ticketsRemaining()` is called 1 time in the view
 - `ticketsSold()` is called 2 times in the view
 - `totalTickets()` is called 2 times in the view
 - `ticketsSold()` and `totalTickets()` are called once every time `percentageSoldOut` is called and it is called 2 times in the view.

This gives us 1 + 2 + 2 + 2 * (1 + 1) = 9 queries, out of which 6 are duplicates. Eloquent provides the ability to load aggregate values lazily, so by doing so, we can eliminate every duplicate query. If the count changes, refreshing the model will wipe the loaded aggregates.

```php
public function ticketsRemaining(): int
{
    return $this->tickets_remaining ?? $this->loadCount([
        'tickets as tickets_remaining' => fn (Builder $tickets) => $tickets->available(),
    ])->tickets_remaining;
}

public function ticketsSold(): int
{
    return $this->tickets_sold ?? $this->loadCount([
        'tickets as tickets_sold' => fn (Builder $tickets) => $tickets->sold(),
    ])->tickets_sold;
}

public function totalTickets(): int
{
    return $this->total_tickets ?? $this->loadCount([
        'tickets as total_tickets',
    ])->total_tickets;
}

public function percentSoldOut(): float
{
    return number_format(num: 100 * $this->ticketsSold() / $this->totalTickets(), decimals: 2);
}
```

To avoid the duplication while keeping the `order()` relationship, loading the collection itself in the `revenueInDollars()` can fix.

```php
public function revenueInDollars(): float
{
     return $this->orders()->cursor()->unique('id')->sum('amount') / 100;
}
```

### Chapter 24

Current model factories don't provide a direct method to multiple pivots at the same time, but it's possible to go around the belongsToMany relationship by going through the pivot hasMany -> belongsTo relationship

```php
$concert = Concert::factory()->create();
$order = Order::factory()->has(
    Ticket::factory()->count(3)->for($concert)
);
```

### Chapter 25

Since Laravel 6.x, the method `cursor()` allows to iterate over every single result of a query while only keeping one Eloquent model in memory at a time, lowering memory consumption. Similarly, `lazy($chunkSize = 1000)` allows to chunk the results all the while keeping them in a flattened `LazyCollection`.

```php
// ceil(User::count() / 200) queries, Collection of 200 users in memory.
User::chunk(200, function ($users) {
    $users->each(function (User $user) {
        //...
    });
});
```
```php
// Single query, only 1 model in memory at any given time.
User::cursor()->each(function (User $user) {
    //...
});
```
```php
// ceil(User::count() / 200) queries, only 1 model in memory at any given time.
User::lazy(200)->each(function (User $user) {
    //...
});
```
Instead of `withRecipients(callback)`, I opted to keep the `recipients()->each(...)` approach. Using lazy collections and some specific PostgreSQL logic
```php
public function recipients(): LazyCollection
{
    return $this->concert->orders()                 // get BelongsToMany object
        ->getQuery()                                // get Builder object so we can override select values
        ->distinct()->select('email')               // get only the emails column, make it distinct
        ->lazyById(chunkSize: 20, column: 'email')  // chunk lazily. Default column is 'id'.
                                                    // since 'id' is not present in select clause,
                                                    // PostgreSQL will throw an error so we specify email.
        ->pluck('email');                           // pluck 'email' column, returning a "chunked" LazyCollection of emails.
}
```

### Chapter 26

Considering the folder where test files are stored get emptied at the start of the test, this implementation works
```php
$this->assertFileEquals(
    expected: $file->getPathname(),
    actual: Storage::disk('s3')->path("concerts/{$concert->poster_image_path}")
);
```
but we could also just check the hash names.
```php
Storage::disk('s3')->assertExist("posters/{$file->hashName()}");
```
The null-safe `?->` operator in PHP8 makes it easy to deal with optional files without having to use a Null Object directly.

```php
$concert = Auth::user()
    ->concerts()
    ->create(attributes: $request->safe()->except(['poster_image']) + [
        'poster_image_path' => $request->safe()->poster_image?->store(path: 'posters', options: ['disk' => 's3']),
    ]);
```
The `optional()` helper can also return a null object
```php
$request->file('poster_image', optional())->store(path: 'posters', options: ['disk' => 's3']);
optional($request->file('poster_image'))->store(path: 'posters', options: ['disk' => 's3']);
```

### Chapter 27

Creating an Event fired at the only place in the application where a Concert is created seems a bit unnecessary considering Eloquent provides events of its own.

Instead of having to do `ConcertAdded::dispatch($concert)`, we can simply attach a listener to the `Concert::created` event. Admittedly, this can cause trouble if we decide to create concerts somewhere else in the application, or even in factories and seeders.

Laravel provides a way to test if a listener is attached to an event with `Event::assertListening`.
```php
Event::assertListening(
    expectedEvent: ConcertAdded::class,
    expectedListener: SchedulePosterImageProcessing::class,
);
```

### Chapter 28

Instead of using `getimagesizefromstring(string $string, &$image_info = null)`, we can use `getimagesize(string $filename, &$image_info = null)` passing the path, but that might not work with non-local drivers

```php
$resizedImage = Storage::disk(name: 'public')->get(path: 'posters/example-poster.png');

[$width] = getimagesizefromstring(string: $resizedImage);
```
```php
$resizedImagePath = Storage::disk(name: 'public')->path(path: 'posters/example-poster.png');

[$width] = getimagesize(filename: $resizedImagePath);
```
