<?php

declare(strict_types=1);

it('can test', function (): void {
    expect(true)->toBeTrue();
});

it('can use facade alias', function (): void {
    expect(TaskFlow::example())->toBeTrue();
});
