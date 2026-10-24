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

A consumer ends the pipeline instead of yielding another lazy iterable.

## Built-in PHP array functions not currently represented

These are the strongest candidates that match the same constraints as the current library: either lazy by default, or stateful but still valid as an iterator transform.

- `array_chunk`
- `array_column`
- `array_flip`
- `array_combine`
- `array_diff`
- `array_diff_assoc`
- `array_diff_key`
- `array_intersect`
- `array_intersect_assoc`
- `array_intersect_key`
- `array_count_values`
- `array_merge`
- `array_replace`
- `array_replace_recursive`
- `array_pad`

### Why these fit

These can all be understood as transforms over iterable input, even if they need internal tracking or buffering to maintain the semantics of the native array function.

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
3. Add the missing transform-style built-ins incrementally, starting with the simplest and most obviously stream-friendly ones.
4. Keep sorting and other global-reordering functions out of the core lazy pipeline unless a separate buffering or materialization strategy is explicitly introduced.
5. Preserve the distinction between:
   - lazy transformations, and
   - terminal consumers.

## Suggested next additions

A sensible first wave would be:

- `array_chunk`
- `array_column`
- `array_flip`
- `array_diff`
- `array_intersect`
- `array_count_values`

These are easy to reason about as iterable transforms and do not conflict with the library’s current design.
