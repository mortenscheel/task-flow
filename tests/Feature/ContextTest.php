<?php

declare(strict_types=1);

namespace Scheel\TaskFlow\Tests\Feature;

use RuntimeException;
use Scheel\TaskFlow\Context;

use function app;

it('can get, set and check values', function (): void {
    $context = app(Context::class);

    expect($context->has('foo'))->toBeFalse()
        ->and($context->get('foo'))->toBeNull()
        ->and($context->get('foo', 'fallback'))->toBe('fallback');
    $context->set('foo', 'bar');

    expect($context->has('foo'))->toBeTrue()
        ->and($context->get('foo'))->toBe('bar');
});

it('can increment values', function (): void {
    $context = app(Context::class);

    expect($context->has('foo'))->toBeFalse()
        ->and($context->get('foo'))->toBeNull();
    $context->increment('foo');

    expect($context->has('foo'))->toBeTrue()
        ->and($context->get('foo'))->toBe(1);
    $context->increment('foo', 2);

    expect($context->has('foo'))->toBeTrue()
        ->and($context->get('foo'))->toBe(3);

    $context->set('bar', 2);
    $context->increment('bar', 3);

    expect($context->get('bar'))->toBe(5);
});

it('throws when incrementing a non-integer value', function (): void {
    $context = app(Context::class);
    $context->set('foo', 2.2);

    $context->increment('foo');
})->throws(RuntimeException::class, 'Attempt to increment a non-integer value');
