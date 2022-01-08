![Discrete units of logic for PHP](https://banners.beyondco.de/Actions.png?theme=light&packageManager=composer+require&packageName=stagerightlabs%2Factions&pattern=floatingCogs&style=style_1&description=Discrete+units+of+logic+for+PHP&md=1&showWatermark=1&fontSize=100px&images=beaker)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stagerightlabs/actions.svg?style=flat)](https://packagist.org/packages/stagerightlabs/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/stagerightlabs/actions.svg?style=flat)](https://packagist.org/packages/stagerightlabs/actions)
[![Tests](https://github.com/stagerightlabs/actions/actions/workflows/ci.yml/badge.svg)](https://github.com/stagerightlabs/actions/actions/workflows/ci.yml)

# Actions

Action classes are a pattern for isolating business logic from the rest of an application. This makes it easier to test the business logic and it provides portability: you can use the same action class in multiple contexts which helps to DRY up your codebase.

This package provides a foundation for creating action classes that will integrate easily with the rest of your PHP application.

## Inspiration

This idea has been in the back of my mind for some time. However, these blog posts really helped me shape my ideas about how Action classes should work and inspired me to create this package:

- [Brent Roose - Actions](https://stitcher.io/blog/laravel-beyond-crud-03-actions)
- [Brent Roose - Domain Oriented Laravel](https://stitcher.io/blog/laravel-beyond-crud-01-domain-oriented-laravel)
- [Freek Van der Herten - Refactoring to Actions](https://freek.dev/1371-refactoring-to-actions)

## Installation

Install this library with [Composer](https://getcomposer.org/):

```
$ composer require stagerightlabs/actions
```

## Instructions

This library provides an abstract `Action` class that you can extend to create your own action classes. All you need to do is provide a `handle` method which contains your business logic. For example:

```php
<?php

namespace App\Actions;

use StageRightLabs\Actions\Action;

class MyCoolAction extends Action
{
  public function handle($input = [])
  {
    // business logic goes here...

    return $this->complete('Hooray, it worked!');
  }
}
```

To use this class in your application, you can call the static `execute()` method:

```php
<?php

namespace App\Http\Controllers;

use App\Actions\MyCoolAction;

class Controller
{
  public function post()
  {
    $action = MyCoolAction::execute();

    if ($action->failed()) {
      // send an alert
      return;
    }

    // do something else
    return;
  }
}
```

Notice how we generate our response based on the outcome of the action.

### Input

It is not often that business logic can be performed without some sort of input. We can provide input to our action class by providing it with an associative array of data. To ensure that the action is not executed without everything in place, the class will verify the keys of that input array before your task is run.

```php
<?php

namespace App\Actions;

use StageRightLabs\Actions\Action;

class UserCreationAction extends Action
{
  public function handle($input = [])
  {
    // User is created here...

    return $this->complete();
  }

  public function required()
  {
    return [
      'username',
      'email',
    ]
  }

  public function optional()
  {
    return [
      'timezone'
    ]
  }
}
```

We use the `required()` and `optional()` methods to tell the action what input keys to expect. If a required key is missing the action will fail before it executes. If any input keys are provided that are not defined as required or optional, the action will fail. It is important to note that this validation is only performed on the array keys; if a key is provided but it contains a falsy value, the action will still proceed.

The goal of this validation step is to provide clarity to future developers about how your action classes work and what input they need.

### Completion Status

In the course of performing our business logic we may decide that the action will need to fail or that it has completed. Use the `fail()` and `complete()` methods to set the status of the action and halt further execution:

```php
public function handle($input = [])
{
  if (is_null($user)) {
    return $this->fail('There was a problem creating your account.');
  }

  return $this->complete('Your new account has been created.');
}
```

The message is optional. It can be accessed like so:

```php
$action = MyCoolAction::execute();

if ($action->failed()) {
  $this->sendAlert($action->getMessage());
  return;
}
```

### Output

Often you may find that your action produces artifacts that you would like to make available elsewhere. To retain these artifacts, assign them to public class properties in your action. This allows the action class itself to behave as a data transfer object:

```php
<?php

namespace App\Actions;

use StageRightLabs\Actions\Action;

class UserCreationAction extends Action
{
  public $user;

  public function handle($input = [])
  {
    $this->user = User::create($input);

    return $this->complete();
  }
}
```

You can then access those artifacts after the execution is complete:

```php
$action = MyCoolAction::execute();

if ($action->failed()) {
  $this->sendAlert($action->getMessage());
  return;
}

$user = $action->user;
```

### Class Constructors

If you would like to define a constructor on your action classes to allow for dependency injection you will not then be able to call the `execute()` method statically. However, you can instead call it from the regular object context:

```php
$action = new MyCoolAction(new SomeDependency);
$action = $action->execute();
```

### API

- `execute($input)`: A static method used to trigger your action. The `$input` array should contain everything your action needs to do its work.
- `handle($input)`: This is where you define the work that your action will perform. If the action class has been instantiated you can call this method directly to trigger the action, but this will skip over the input key validation.
- `required()`: Use this method to define an array of input keys that are required for your action.
- `optional()`: Use this method to define an array of input keys that are allowed but not required by your action.
- `complete($optionalMessage)`: Flag the action as complete and halt execution. You can optionally provide a completion message.
- `fail($optionalMessage)`: Flag th action as failed and halt execution. You can optionally provide a failure message.
- `completed()`: Check the completion status of your action. Returns `true` if the action completed, `false` if it did not.
- `failed()`: Check the failure status of your action. Returns `true` if the action failed, `false` if it did not.
- `getMessage()`: Retrieve the message that was set when you called either `complete()` or `fail()`.
