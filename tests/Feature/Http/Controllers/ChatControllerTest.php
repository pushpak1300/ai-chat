<?php

declare(strict_types=1);

test('example', function (): void {
    $response = $this->get('/');

    $response->assertStatus(200);
});
