# Iterators

A small PHP library for applying familiar array-style operations to any `iterable`, including arrays, generators, and other traversables.

The library is currently exploring a strategy-based design:

- **Operations** transform an iterable and return another iterable.
- **Consumers** terminate a pipeline and return a final value.
- `LazyIterator` dispatches method names to operation or consumer objects.

Operations and consumers are invokable, so they can be used directly or through the fluent iterator API.

## Requirements

- PHP 8.2+
- Composer

## Installation

```bash
composer install
```

## Basic usage

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Iterators\LazyIterator;

$evenSquares = LazyIterator::from([1, 2, 3, 4, 5])
    ->filter(fn (int $value): bool => $value % 2 === 0)
    ->map(fn (int $value): int => $value * $value)
    ->values();

foreach ($evenSquares as $value) {
    echo $value, PHP_EOL;
}
// 4
// 16

$total = LazyIterator::from([1, 2, 3])->sum();
// 6
```

Operations are lazy. The callbacks in this example do not run until the result is iterated.

## Current API

### Operations

| Method                                                                | Description                                                                                    |
| --------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------- |
| `map(callable $callback)`                                             | Lazily transforms each value. The callback receives the value and key.                         |
| `filter(?callable $callback = null, int $mode = 0)`                   | Lazily filters values. Supports `ARRAY_FILTER_USE_KEY` and `ARRAY_FILTER_USE_BOTH`.            |
| `keys(mixed $searchValue = null, bool $strict = false)`               | Yields keys, optionally matching values like `array_keys()`.                                   |
| `values()`                                                            | Yields values and reindexes the output.                                                        |
| `slice(int $offset, ?int $length = null, bool $preserveKeys = false)` | Lazily yields a slice of the input.                                                            |
| `reverse(bool $preserveKeys = true)`                                  | Reverses the input. This buffers the source because reverse order requires the complete input. |
| `unique(?callable $keySelector = null)`                               | Yields the first item for each unique value or selector result.                                |

### Consumer

| Method  | Description                                    |
| ------- | ---------------------------------------------- |
| `sum()` | Consumes numeric values and returns their sum. |

A consumer ends the pipeline:

```php
$total = LazyIterator::from([1, 2, 3])
    ->map(fn (int $value): int => $value * 2)
    ->sum();
// 12
```

## Strategy pattern

`LazyIterator` contains a dispatch table that maps fluent method names to handler classes:

```php
private static array $dispatch = [
    'map' => MapOperation::class,
    'sum' => SumConsumer::class,
];
```

Each handler is a strategy with a common invokable contract:

```php
interface IteratorOperation
{
    public function __invoke(iterable $items, mixed ...$args): iterable;
}

interface IteratorConsumer
{
    public function __invoke(iterable $items, mixed ...$args): mixed;
}
```

This keeps `LazyIterator` small. Adding an operation generally requires a new class and one dispatch-table entry, rather than another wrapper implementation with duplicated pipeline logic.

Handlers can also be used directly:

```php
use Iterators\Operations\MapOperation;

$map = new MapOperation();
$result = $map([1, 2, 3], fn (int $value): int => $value * 10);

foreach ($result as $value) {
    // 10, 20, 30
}
```

## Testing

Run the PHPUnit suite with:

```bash
vendor/bin/phpunit
```

The tests cover direct invokable handlers, lazy callback execution, operation chaining, consumer results, and unsupported method errors.

## Current gaps and known issues

- The API is still experimental and may change as more array-compatible operations are added.
- `reverse()` must buffer the complete input, so it is not constant-memory for generators or large API responses.
- `unique()` retains all previously seen values or selector results, so memory usage grows with the input.
- `slice()` is optimized for non-negative offsets. Negative offsets and negative lengths are not currently implemented with full `array_slice()` semantics.
- `map()` currently supports one iterable source and preserves source keys; it does not yet implement every behavior of PHP's multi-array `array_map()`.
- `filter()` is callback-compatible with the current supported modes, but PHP's exact warning and error behavior is not reproduced in every invalid-input case.
- `OrderedIterator` is not integrated with the current `LazyIterator` strategy dispatch and is intentionally outside the current test scope.
- The current consumer set is small. Additional terminal strategies such as `count`, `reduce`, `any`, and `all` are natural future additions.
- There is no static type-safe fluent method surface yet; PHPDoc `@method` annotations provide editor assistance for the current operations.

## Design direction

The intended direction is to separate concerns clearly:

1. Keep transformations lazy whenever the operation can make progress without seeing the entire source.
2. Make operations reusable independently of `LazyIterator`.
3. Represent terminal actions as consumers so they cannot accidentally be chained as transformations.
4. Add buffering or external-storage strategies explicitly for operations that require global knowledge, such as sorting.
5. Keep the fluent wrapper as a dispatcher rather than a long list of repetitive methods.

## On the Use of GenAI

This project is being developed as a bridge for a problem observed with PHP's built-in `array_*` functions. It is not intended for mass distribution or monetization, but is being made available for those who observe the same problem and are likewise exploring areas of improvement.

A significant portion of this codebase has been produced with the assistance of various generative AI tools. This is not intended as a statement of replacement for human involvement, but rather as an acknowledgment of how the project has been shaped. The code generated in this context is subject to scrutiny, revision, and improvement to align with our goals.

This notice is simply to make clear that AI-assisted development is part of the project’s workflow, without implying any intent to remove or diminish the role of human oversight in the creation and maintenance of the software.
