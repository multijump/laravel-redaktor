# Laravel Redaktør

Laravel Redaktør is a simple yet powerful API versioning library based around the idea of API Evolution. It provides a way to develop your API in a tidy and elegant way while maintaining backwards compatibility.

Inspired by [Stripe's API versioning](https://stripe.com/blog/api-versioning) strategy.

## Table of Contents
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Concepts](#concepts)
    * [Versioning](#versioning)
        + [Custom Header](#custom-header)
        + [URI Path](#uri-path)
        + [Query String](#query-string)
        + [_Custom Strategy_](#_custom-strategy_)
    * [Revisions](#revisions)
        + [Routing Revisions](#routing-revisions)
        + [Request Revisions](#request-revisions)
        + [Response Revisions](#response-revisions)
        + [Message Revisions](#message-revisions)
    * [Registry](#registry)
    * [Route Tagging](#route-tagging)
- [Testing](#testing)
- [License](#license)

## Requirements

* PHP 7.3 or higher.
* Laravel 6 or higher.

## Installation

To install the latest version, simply require it from your CLI:
```sh
  composer require ds-labs/laravel-redaktor
```

Service Provider(s) and/or Facade(s) will be auto discovered.

## Configuration

Publish the configuration file:
```sh
  php artisan vendor:publish --provider="DSLabs\LaravelRedaktor\RedaktorServiceProvider"
```
The above command will create a `redaktor.php` file in your `/config` directory.

## Quick Start

Assuming there is a `GET /api/users` endpoint that renders a non-paginated list of users, and you want to evolve our API to paginate the list of results...

1. Make the necessary changes to your application for the `GET /api/users` endpoint returns a paginated list of users.

1. Create a backwards compatibility revision in an `app/Http/Revisions` directory:
    ```php
        namespace App\Http\Revisions;

        final class RemovePaginationMetadata implements \DSLabs\Redaktor\Revision\ResponseRevision
        {
            /**
            * @param \Illuminate\Http\Request $request
            */
            public function isApplicable(object $request): bool
            {
                return $request->is('api/users') && $request->isMethod('GET');
            }
        
            /**
             * @param \Illuminate\Http\Request $request
             * @param \Illuminate\Http\JsonResponse $response
             *
             * @return \Illuminate\Http\Response
             */
            public function applyToResponse(object $request, object $response): object
            {
                $json = \json_decode($response->content(), true);
        
                return $response->setData(
                    $json['data']
                );
            }
        }
    ```

1. Register the revision in the [published config](#Configuration) file (`config/redaktor.php`):
   ```php
      return [
      
          // ...
      
          /*
           * Add here your Revision definitions, indexed by its version name.
           */
          'revisions' => [
   
              '2020-10-20' => [
                   \App\Http\Revisions\RemovePaginationMetadata::class,
              ],
      
          ],

      ];
    ```

1. Try it!!!
   
   A request to `GET /api/users` with an `API-Version:2020-10-20` header will return the original response containing the non-paginated list of users; whereas a request to the same endpoint without the version header will respond with the paginated list of users.

## Concepts

### Versioning

API versioning can be implemented using various strategies; all of them with pros and cons. For this reason, Redaktør does not make any assumption and provides an implementation for the most commonly used strategies.

A strategy is responsible to identify the intended target version. That is, to find out what version of the API the request sender is trying to interact with.

Redaktør does not make any assumptions about your version naming convention; however, it advises to use a date-based version naming approach, where each version name is the date when the revision was implemented/released. I.e.: `2020-10-20`.

By default, a custom `API-Version` header specifying the target version is expected. This default can be configured by modifying the `strategies` configuration in the published `redaktor.php` configuration file, as shown in the following sections.

If no version is defined in the request, or it is defined but does not match an existing one, the latest version is presumed.

#### Custom Header

APIs using a custom header as their versioning strategy may use the `\DSLabs\LaravelRedaktor\Version\CustomHeaderStrategy` strategy.

The Custom Header strategy is configured as the default strategy. It expects an `API-Version` header indicating the version to be used. E.g.:
```text
   GET /api/users HTTP/1.1
   Host: example.org
   API-Version: 2020-10-20
```

If you would like to use a different header name, just modify the value of the `name` property in the `redaktor.php` configuration file:

```php
   return [
   
       /*
        * Configure the version resolver strategies.
        */
       'strategies' => [
           [ 
               'id' => \DSLabs\LaravelRedaktor\Version\CustomHeaderStrategy::class,
               'config' => [
                   'name' => 'Version',
               ],
           ],
       ],
   
       // ...
       
   ];
```

#### URI Path

APIs defining the version in the URI (i.e.: `/api/v1/users`) may use `\DSLabs\LaravelRedaktor\Version\UriPathStrategy` as their versioning strategy.

The URI Path strategy extracts the target version from the `index` position (0-based) of the URI path segments. That is, setting `index` to `1` will return `2020-10-20` as the target version for the following request:
```text
   GET /api/2020-10-20/users HTTP/1.1
   Host: example.org
```

To use this strategy, override the `strategies` configuration in the `/config/redaktor.php` file:

```php
   return [

       /*
        * Configure the version resolver strategies.
        */
       'strategies' => [
           [
               'id' => \DSLabs\LaravelRedaktor\Version\UriPathStrategy::class,
               'config' => [
                   'index' => 1,
               ],
           ],
       ],

      // ...

   ];
```

#### Query String

APIs accepting the version as a query string parameter, may use the
`\DSLabs\LaravelRedaktor\Version\QueryStringStrategy` strategy.

The parameter name can be configured by changing the `name` property in the `/config/redaktor.php` configuration file:

```php
   return [
   
       /*
        * Configure the version resolver strategies.
        */
       'strategies' => [
            [
               'id' => \DSLabs\LaravelRedaktor\Version\QueryStringStrategy::class,
               'config' => [
                  'name' => 'version',
               ],
           ],
       ],    
   
      // ...
      
   ];
```

#### _Custom Strategy_

If none of the provided implementations suits your API's versioning strategy, you can create your own by implementing the `\DSLabs\Redaktor\Version\Strategy` interface.

To use your custom strategy, set its Full Qualified Class Name (FQCN) or alias as the `id` property in `/config/redaktor.php`; the `config` array will be passed as the `$parameters` argument to the Container's `make()` method, giving you the chance to configure your custom strategy from the config file.

### Revisions

Also known as Backwards Compatibility (BC) Revisions, encapsulate the necessary changes to maintain backwards compatibility across API versions.

Every time a backwards incompatible change is introduced in the API, a new revision shall be created.

You may place your Revisions anywhere you wish. However, applications following Laravel default folder structure are recommended to place them in an `App\Http\Revisions` namespace.

Depending on the type of breaking change, a specific type of Revision shall be used. Let's have a look at the different types:

#### Routing Revisions

Routing Revisions provide a way to modify the application routing definitions. They let you change the request path, parameter constraints, middlewares, etc. associated to your application routes.

To illustrate how Routing Revisions work, consider an API defining a set of endpoints prefixed by `/api` (e.g.: `/api/users`). At some point we may decide that the prefix is unnecessary, and we would like to drop it. To achieve it:

1. Update the route definitions to drop the `/api` prefix.

1. Create the Routing revision:
   ```php
      namespace App\Http\Revisions;
   
      final class AddApiPrefix implements \DSLabs\Redaktor\Revision\RoutingRevision
      {
          /**
           * @param \Illuminate\Routing\RouteCollection $routes
           * @return \Illuminate\Routing\RouteCollection
           */
          public function __invoke(iterable $routes): iterable
          {
              foreach ($routes as $route) {
                  $route->prefix('api');
              }
      
              return $routes;
          }
      }
   ```

1. Register the revision.
   ```php
      return [
      
          // ...
      
          /*
           * Add here your Revision definitions, indexed by its version name.
           */
          'revisions' => [
   
              '2020-10-20' => [
                   \App\Http\Revisions\AddApiPrefix::class,
              ],
      
          ],
      
      ];
    ```

With the BC version registered, requests specifying the `2020-10-20` version will be able to carry on making requests using the `/api` prefix (e.g.: `/api/users`), whereas requests not indicating the version will have to be made to the unprefixed version of the endpoints (e.g.: `/users`).

#### Request Revisions

When backwards incompatible changes are introduced in the request, an instance of a Request Revision shall be used.

Request Revisions are classes implementing the `DSLabs\Redaktor\Revision\RequestRevision` interface; it defines two methods:
* `isApplicable()`, which indicates when the revision needs to be revised for the given `$request`. If a `true` value is returned the Request will be passed on to the `applyToRequest()` method. Otherwise, it will be skipped.
* `applyToRequest()`, where the `$request` is edited to make it compatible with the changes introduced in the application.

To better explain Request Revision, let's have a look at an example. The following code shows a Request Revision that maintains backwards compatibility when an API evolves the `/api/users` resource from accepting an `age` property as a number (e.g.: `25`) to a `date_of_birth` property taking a date-formatted string (e.g.: `2005-09-16`).

```php
   final class ReplaceAgeByDateOfBirth implements \DSLabs\Redaktor\Revision\RequestRevision
   {
       /**
        * @var \Illuminate\Http\Request $request
        */
       public function isApplicable(object $request): bool
       {
           return $request->is('api/users/*') &&
               (
                   $request->isMethod('POST') ||
                   $request->isMethod('PUT')
               );
       }
   
       /**
        * @param \Illuminate\Http\Request $request
        *
        * @return \Illuminate\Http\Request
        */
       public function applyToRequest(object $request): object
       {
           $payload = $request->json();
   
           $age = $payload->get('age');
           $inferredDateOfBirth = \Carbon\Carbon::parse("-$age years")->toDateString();
   
           // Add new `date_of_birth` property.
           $payload->set('date_of_birth', $inferredDateOfBirth);
   
           // Remove no longer supported `age` property.
           $payload->remove('age');
   
           return $request;
       }
   }
```

It's worth noting that due to the new version requiring a more precise value, the above revision uses the current day and month to infer the new `date_of_birth` value.

After registering the revision above as version `2020-10-20`, the following requests would both be successful:
* Request using version `2020-10-20`:
   ```text
      POST /api/users/1 HTTP/1.1
      Host: example.org
      API-Version: 2020-10-20
      Content-Type: application/json
      Content-Length: 78
      
      {
         "name": "John Doe",
         "email": "john.doe@example.org",
         "age": 25
      }
   ```

* Request using latest version:
   ```text
      POST /api/users/1 HTTP/1.1
      Host: example.org
      Content-Type: application/json
      Content-Length: 98
      
      {
         "name": "John Doe",
         "emaiil": "john.doe@example.org",
         "date_of_birth": "2005-09-16"
      }
   ```

#### Response Revisions

Response Revisions are pretty much the same as Request Revisions, apart from the fact that they are used, as its name implies, to edit the Response rather than the Request.

A Response Revision shall implement the `DSLabs\Redaktor\Revision\ResponseRevision` interface, which defines two methods:
* `isApplicable()`, same as in Request Revisions this method evaluates the scenario(s) in which the revision is applicable for the given `$request`. If a `true` value is returned the Request will be passed on to the `applyToResponse()` method.
* `applyToResponse()`, where the `$response` will be edited to maintain compatibility with the new breaking changes.

An example of a Response revision could look like the following:
```php
    final class DropPaginationMetadata implements \DSLabs\Redaktor\Revision\ResponseRevision
    {
        /**
         * @inheritDoc
         *
         * @var \Illuminate\Http\Request $request
         */
        public function isApplicable(object $request): bool
        {
            return $request->isMethod('api/users')
                && $request->isMethod('GET');
        }
    
        /**
         * @param \Illuminate\Http\Request $request
         * @param \Illuminate\Http\JsonResponse $response
         *
         * @return \Illuminate\Http\JsonResponse
         */
        public function applyToResponse(object $request, object $response): object
        {
            $response->setData(
                $response->getData(true)['data']
            );
    
            return $response;
        }
    }
```

The above revision demonstrates how to keep backwards compatibility when evolving an `/api/users` endpoint form been a basic list of users to a paginated list.

#### Message Revisions

Message Revisions merge both Request and Response revisions into a single easy-to-use revision type. They are useful in situations when both the request and the response are modified for the same request.

Creating a Message Revision is as simple as implementing the `DSLabs\Redaktor\Revision\MessageRevision` interface. The interface defines a combination of the methods defined by the two interfaces it is composed of, `DSLabs\Redaktor\Revision\RequestRevision` and `DSLabs\Redaktor\Revision\ResponseRevision`. For further details check each revision type section above.

### Registry

The registry holds the list of available versions with the revision definitions registered for each version.

A revision definition is simply a string or closure used to define a revision. It can be one of the following:
* A revision FQCN or alias:
    ```php
        \App\Http\Revisions\MyCustomRevision::class
    ```  
* A closure returning a revision FQCN or alias:
    ```php
        function (): string {
            return \App\Http\Revisions\MyCustomRevision::class;
        }  
    ```
* A closure returning a revision instance:
    ```php
        function (): Revision {
            return new \App\Http\Revisions\MyCustomRevision();
        }  
    ```

Each new version must be added at the end of the `revisions` array.

To register a version, add a list of revision definitions indexed by its version name to the `revisions` key in `config/redaktor.php` as shown below:
```php
   return [
    
    // ...
    
    /*
     * Add here your Revision definitions, indexed by its version name.
     */
    'revisions' => [
          '2020-06-17' => [
              ConvertActivePropertyToActivatedAt::class,
              ReplaceAgeByDateOfBirth::class,
         ],
         '2020-10-20' => [
            PaginateUsersList::class,
        ],
    ],

];
```

### Route Tagging

Sometimes in a revision, you may have the need to target certain route(s). You could do so by naming each of them in your route definition and checking in your revision if the name is the one expected. However, this becomes tedious and brittle when you are targeting a bunch of routes.

To address this, Laravel Redaktør ships with a feature that allows you to tag and check for tags in your routes easily.

Tag a route:
```php
    Route::get('/users', [UserController::class, 'index'])
        ->tag('a_tag');
```

Alternatively, you may pass an array of tags:
```php
    Route::get('/users', [UserController::class, 'index'])
        ->tag(['a_tag', 'another_tag']);
```

Get a list of tags that a route has been tagged with:
```php
    $route = Route::get('/users', [UserController::class, 'index'])
        ->tag(['a_tag', 'another_tag']);

    $route->tags(); // ['a_tag', 'another_tag']
```

Check whether a route contains a tag:
```php
    $route = Route::get('/users', [UserController::class, 'index'])
        ->tag('a_tag');

    $route->hasTag('a_tag'); // true
    $route->hasTag('another_tag'); // false
```

You may also tag route groups, which will result in all routes within the group tagged:
```php
    Route::group(['tags' => ['a_tag']], function () {
        Route::get('/users', [UserController::class, 'index']);
    });
```
The `GET /users` route is tagged as `a_tag`.

The Router instance provides a handy `getByTag()` method that will return a list of routes tagged with a given tag:
```php
    Router::getByTag('a_tag');
```

## Testing

Running all tests:
```sh
   composer test
```

## License

Laravel Redaktør is an open-source software licensed under the [MIT license](LICENSE.md).
