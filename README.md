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