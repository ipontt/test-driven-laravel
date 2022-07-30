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

Dusk can be flimsy at times. It is a powerful tool though.

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
