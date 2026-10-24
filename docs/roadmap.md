# Iterator library roadmap

## Current lazy transform set

These are already represented in the library as lazy iterator operations, or are natural matches for the same model:

- `map`
- `filter`
- `keys`
- `values`
- `slice`
- `reverse`
- `unique`

### Interpretation

- `map`, `filter`, `keys`, `values`, and `slice` are the core streaming-style transforms.
- `reverse` is lazy as a transformation, but it must buffer the input before yielding.
- `unique` is lazy as a transformation, but it keeps seen values or selector results in memory while iterating.

These are still valid iterator transforms even though they require state internally.

## Terminal / non-transform operations

These are not lazy transforms and should not be treated as iterator operations:

- `sum`
- `count_values`

A consumer ends the pipeline instead of yielding another lazy iterable.
`count_values` returns counts for integer and string values and emits PHP's warning for unsupported values, which are skipped.

## Implemented array-compatible operations

These fluent operations use the corresponding PHP built-in behavior. The source iterable replaces the built-in's first array argument; remaining arguments keep the built-in's order and meaning.

- `chunk`
- `column`
- `flip`
- `combine`
- `diff`
- `diff_assoc`
- `diff_key`
- `intersect`
- `intersect_assoc`
- `intersect_key`
- `merge`
- `replace`
- `replace_recursive`
- `pad`

The source is materialized when iterating the result, then passed to the native array function. This preserves PHP's key handling, comparison rules, and errors while still accepting generators and other iterables.

## Functions to deliberately exclude

These do not fit the library’s lazy-iterator model and should be treated as out of scope:

- `array_sort`
- `sort`
- `rsort`
- `usort`
- `uasort`
- `uksort`
- `array_sum`
- `array_product`
- `array_reduce`
- `array_walk`
- `array_walk_recursive`
- `shuffle`
- `array_rand`

### Why these are excluded

They either:

- require full reordering of the entire collection,
- terminate the stream,
- perform side effects instead of yielding a transformed iterable,
- or are inherently non-streaming operations.

## Recommended roadmap direction

1. Keep the iterator library focused on transforms that yield another iterable.
2. Treat stateful operations like `reverse` and `unique` as valid lazy operations, so long as they do not require the caller to materialize the entire collection up front.
3. Add further transform-style built-ins incrementally, verifying their behavior against the corresponding PHP functions.
4. Keep sorting and other global-reordering functions out of the core lazy pipeline unless a separate buffering or materialization strategy is explicitly introduced.
5. Preserve the distinction between:
   - lazy transformations, and
   - terminal consumers.

## Suggested next additions

All operations in the original array-function candidate list are now represented. Consider additional built-ins only when their native semantics fit the iterable API and can be preserved reliably.
